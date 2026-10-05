<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupService
{
    protected string $backupDir;

    /**
     * خريطة: driver => معلومات أساسية
     */
    protected const SUPPORTED_DRIVERS = [
        'mysql'    => [
            'binary'  => 'mysqldump',
            'env_key' => 'MYSQLDUMP_PATH',
        ],
        'pgsql'    => [
            'binary'  => 'pg_dump',
            'env_key' => 'PGDUMP_PATH',
        ],
    ];

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /* ============================================================
       إنشاء نسخة احتياطية
       ============================================================ */
    public function create(string $type = 'auto'): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $baseName  = "backup_{$timestamp}_{$type}";
        $sqlFile   = "{$this->backupDir}/{$baseName}.sql";
        $zipFile   = "{$this->backupDir}/{$baseName}.zip";

        try {
            $driver = $this->getDriver();

            // 1. تصدير قاعدة البيانات (MySQL أو PostgreSQL)
            $this->dumpDatabase($sqlFile, $driver);

            if (!File::exists($sqlFile) || File::size($sqlFile) === 0) {
                throw new \RuntimeException('فشل تصدير قاعدة البيانات (ملف فارغ)');
            }

            // 2. إنشاء ملف metadata
            $metaFile = "{$this->backupDir}/{$baseName}.meta.json";
            File::put($metaFile, json_encode([
                'created_at'   => now()->toIso8601String(),
                'type'         => $type,
                'driver'       => $driver,
                'database'     => $this->getDatabaseName(),
                'host'         => $this->getDbConfig('host'),
                'size_sql'     => File::size($sqlFile),
                'app_version'  => config('app.version', '1.0.0'),
                'php_version'  => PHP_VERSION,
                'laravel'      => app()->version(),
                'restore_note' => $driver === 'pgsql'
                    ? 'استخدم: psql -U {user} -d {database} < backup.sql'
                    : 'استخدم: mysql -u {user} -p {database} < backup.sql',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // 3. ضغط SQL + meta في ZIP
            $this->zipFiles([$sqlFile, $metaFile], $zipFile);

            // 4. حذف الملفات المؤقتة
            @unlink($sqlFile);
            @unlink($metaFile);

            if (!File::exists($zipFile)) {
                throw new \RuntimeException('فشل إنشاء ملف ZIP');
            }

            // 5. تنظيف النسخ القديمة
            $deleted = $this->cleanOldBackups(30);

            Log::info("✅ Backup created: {$baseName}.zip", [
                'driver'      => $driver,
                'size'        => File::size($zipFile),
                'deleted_old' => $deleted,
            ]);
            \App\Services\AuditLogger::critical(
                'backup_created',
                null,
                "إنشاء نسخة احتياطية: {$baseName}.zip",
                [],
                ['size' => File::size($zipFile), 'driver' => $driver],
                ['type' => $type]
            );
            return [
                'success'     => true,
                'file'        => "{$baseName}.zip",
                'driver'      => $driver,
                'size'        => File::size($zipFile),
                'created_at'  => now()->toIso8601String(),
                'deleted_old' => $deleted,
            ];

        } catch (\Throwable $e) {
            // تنظيف الملفات الفاشلة
            @unlink($sqlFile);
            @unlink($zipFile);

            Log::error('❌ Backup failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

        /* ============================================================
       استعادة نسخة احتياطية
       ============================================================
       flow:
         1. تحقق من وجود ZIP
         2. استخراج ZIP إلى مجلد مؤقت
         3. قراءة meta.json والتحقق من الـ driver
         4. (اختياري) إنشاء backup أمان للوضع الحالي
         5. تنفيذ الاستعادة حسب الـ driver
         6. تنظيف المجلد المؤقت
       ============================================================ */
    public function restore(string $filename, array $options = []): array
    {
        $filename = basename($filename);
        $zipPath  = $this->getPath($filename);

        if (!$zipPath) {
            throw new \RuntimeException('النسخة الاحتياطية غير موجودة');
        }

        // مجلد مؤقت للاستخراج
        $tempDir = storage_path('app/backup_restore_' . uniqid());
        if (!File::makeDirectory($tempDir, 0755, true, true)) {
            throw new \RuntimeException('تعذر إنشاء مجلد مؤقت للاستعادة');
        }

        $restoreStartTime = now();
        $driver = $this->getDriver();

        try {
            /* ============================================================
               1. استخراج ZIP
               ============================================================ */
            $extracted = $this->extractZip($zipPath, $tempDir);
            if (empty($extracted)) {
                throw new \RuntimeException('النسخة الاحتياطية فارغة أو تالفة');
            }

            /* ============================================================
               2. البحث عن ملف SQL و meta.json
               ============================================================ */
            $sqlFile  = $this->findFileWithExtension($tempDir, 'sql');
            $metaFile = $this->findFileWithExtension($tempDir, 'json');

            if (!$sqlFile) {
                throw new \RuntimeException('لم يتم العثور على ملف SQL داخل الأرشيف');
            }

            if (filesize($sqlFile) === 0) {
                throw new \RuntimeException('ملف SQL فارغ');
            }

            /* ============================================================
               3. التحقق من meta.json (driver)
               ============================================================ */
            $meta = [];
            if ($metaFile) {
                $metaContent = File::get($metaFile);
                $meta = json_decode($metaContent, true) ?: [];
            }

            $backupDriver = $meta['driver'] ?? null;
            if ($backupDriver && $backupDriver !== $driver) {
                throw new \RuntimeException(
                    "عدم تطابق: النسخة الاحتياطية من نوع '{$backupDriver}' " .
                    "لكن النظام الحالي يستخدم '{$driver}'. " .
                    "لا يمكن الاستعادة عبر قواعد بيانات مختلفة."
                );
            }

            /* ============================================================
               4. Backup أمان (افتراضياً مفعّل)
               ============================================================ */
            $safetyBackup = null;
            if (!empty($options['safety_backup'])) {
                try {
                    $safetyBackup = $this->create('safety_before_restore');
                } catch (\Throwable $e) {
                    // لا نُفشل العملية — لكن نسجّل
                    Log::warning('فشل إنشاء نسخة أمان قبل الاستعادة: ' . $e->getMessage());
                }
            }

            /* ============================================================
               5. فصل الاتصالات النشطة لتجنب قفل الجداول
               ============================================================ */
            try {
                DB::disconnect();
                DB::purge();
            } catch (\Throwable $e) {
                // تجاهل — بعض الأنواع لا تحتاج
            }

            /* ============================================================
               6. تنفيذ الاستعادة حسب الـ driver
               ============================================================ */
            match ($driver) {
                'mysql' => $this->restoreMysql($sqlFile),
                'pgsql' => $this->restorePostgres($sqlFile),
                default => throw new \RuntimeException(
                    "نوع قاعدة البيانات '{$driver}' غير مدعوم للاستعادة."
                ),
            };

            $duration = $restoreStartTime->diffInSeconds(now());

            Log::info("✅ Backup restored: {$filename}", [
                'driver'        => $driver,
                'duration_sec'  => $duration,
                'sql_size'      => filesize($sqlFile),
                'safety_backup' => $safetyBackup['file'] ?? null,
            ]);
            \App\Services\AuditLogger::critical(
                'backup_restore_completed',
                null,
                "تمت استعادة النسخة الاحتياطية: {$filename}",
                [],
                [
                    'duration_sec'  => $duration,
                    'safety_backup' => $safetyBackup['file'] ?? null,
                ]
            );
            return [
                'success'         => true,
                'file'            => $filename,
                'driver'          => $driver,
                'duration_sec'    => $duration,
                'sql_size'        => filesize($sqlFile),
                'sql_size_human'  => $this->humanSize(filesize($sqlFile)),
                'meta'            => $meta,
                'safety_backup'   => $safetyBackup['file'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::error('❌ Backup restore failed: ' . $e->getMessage(), [
                'filename' => $filename,
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;

        } finally {
            /* ============================================================
               تنظيف المجلد المؤقت في كل الحالات
               ============================================================ */
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    /* ============================================================
       استعادة MySQL — mysql < file.sql
       ============================================================ */
    protected function restoreMysql(string $sqlFile): void
    {
        $db = $this->getDbConfig();

        $host     = $db['host']     ?? '127.0.0.1';
        $port     = $db['port']     ?? '3306';
        $database = $db['database'] ?? '';
        $username = $db['username'] ?? '';
        $password = $db['password'] ?? '';

        if (!$database || !$username) {
            throw new \RuntimeException('إعدادات قاعدة البيانات غير مكتملة');
        }

        $binary = $this->findClientBinary('mysql');

        // ✅ بناء الأمر كمصفوفة (بدون shell)
        $args = [
            $binary,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $username,
            '--default-character-set=utf8mb4',
        ];

        if ($password !== '') {
            $args[] = '--password=' . $password;
        }

        $args[] = $database;

        $process = new Process($args);
        $process->setTimeout(600);

        // ✅ تمرير ملف SQL عبر stdin (يعمل على Windows و Linux)
        $input = fopen($sqlFile, 'r');
        $process->setInput($input);

        try {
            $process->run();
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'mysql restore failed: ' . ($process->getErrorOutput() ?: 'unknown error')
            );
        }
    }

    /* ============================================================
       استعادة PostgreSQL — psql < file.sql
       ============================================================
       نستخدم --single-transaction لضمان atomicity
       ============================================================ */
        protected function restorePostgres(string $sqlFile): void
        {
            $db = $this->getDbConfig();

            $host     = $db['host']     ?? '127.0.0.1';
            $port     = $db['port']     ?? '5432';
            $database = $db['database'] ?? '';
            $username = $db['username'] ?? '';
            $password = $db['password'] ?? '';

            if (!$database || !$username) {
                throw new \RuntimeException('إعدادات قاعدة البيانات غير مكتملة');
            }

            $binary = $this->findClientBinary('pgsql');

            $args = [
                $binary,
                '--host=' . $host,
                '--port=' . $port,
                '--username=' . $username,
                '--dbname=' . $database,
                '--single-transaction',
                '--set=ON_ERROR_STOP=on',
                '--quiet',
            ];

            $process = new Process($args);

            if ($password !== '') {
                $process->setEnv(['PGPASSWORD' => $password]);
            }

            $process->setTimeout(600);
            $process->setInput(fopen($sqlFile, 'r'));
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new \RuntimeException(
                    'psql restore failed: ' . ($process->getErrorOutput() ?: 'unknown error')
                );
            }
        }

    /* ============================================================
       استخراج ZIP إلى مجلد مؤقت
       ============================================================ */
    protected function extractZip(string $zipPath, string $destDir): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('فشل فتح ملف ZIP');
        }

        $extracted = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            // أمان: منع path traversal
            if (str_contains($name, '..') || str_starts_with($name, '/')) {
                continue;
            }

            if ($zip->extractTo($destDir, $name)) {
                $extracted[] = $name;
            }
        }

        $zip->close();
        return $extracted;
    }

    /* ============================================================
       البحث عن ملف بامتداد معين داخل مجلد
       ============================================================ */
    protected function findFileWithExtension(string $dir, string $extension): ?string
    {
        $files = File::files($dir);
        foreach ($files as $file) {
            if (strtolower($file->getExtension()) === strtolower($extension)) {
                return $file->getPathname();
            }
        }
        return null;
    }

    /* ============================================================
       البحث عن binary للعميل (mysql أو psql — وليس dump)
       ============================================================ */
    protected function findClientBinary(string $driver): string
    {
        $map = [
            'mysql' => ['binary' => 'mysql', 'env_key' => 'MYSQL_CLIENT_PATH'],
            'pgsql' => ['binary' => 'psql',  'env_key' => 'PSQL_PATH'],
        ];

        $config = $map[$driver] ?? null;
        if (!$config) {
            throw new \RuntimeException("السائق '{$driver}' غير مدعوم");
        }

        // 1) من .env
        $custom = env($config['env_key']);
        if ($custom && File::exists($custom)) {
            return $custom;
        }

        // 2) مسارات شائعة
        $candidates = $this->getClientBinaryCandidates($driver);
        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        // 3) البحث في PATH
        $finder = new Process(
            PHP_OS_FAMILY === 'Windows'
                ? ['where', $config['binary']]
                : ['which', $config['binary']]
        );
        $finder->run();

        if ($finder->isSuccessful()) {
            $found = trim(explode("\n", trim($finder->getOutput()))[0]);
            if ($found && File::exists($found)) {
                return $found;
            }
        }

        throw new \RuntimeException(
            "لم يتم العثور على {$config['binary']}. " .
            "الرجاء إضافة {$config['env_key']} في ملف .env"
        );
    }

    protected function getClientBinaryCandidates(string $driver): array
    {
        if ($driver === 'mysql') {
            return [
                'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe',
                'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysql.exe',
                'C:/xampp/mysql/bin/mysql.exe',
                'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysql.exe',
                '/usr/bin/mysql',
                '/usr/local/bin/mysql',
                '/opt/homebrew/bin/mysql',
                '/opt/homebrew/opt/mysql-client/bin/mysql',
            ];
        }

        return [
            'C:/laragon/bin/postgresql/bin/psql.exe',
            'C:/laragon/bin/postgresql/pgsql/bin/psql.exe',
            'C:/Program Files/PostgreSQL/16/bin/psql.exe',
            'C:/Program Files/PostgreSQL/15/bin/psql.exe',
            'C:/Program Files/PostgreSQL/14/bin/psql.exe',
            '/usr/bin/psql',
            '/usr/local/bin/psql',
            '/usr/lib/postgresql/16/bin/psql',
            '/usr/lib/postgresql/15/bin/psql',
            '/usr/lib/postgresql/14/bin/psql',
            '/opt/homebrew/bin/psql',
            '/opt/homebrew/opt/postgresql@16/bin/psql',
            '/Applications/Postgres.app/Contents/Versions/latest/bin/psql',
        ];
    }

    /* ============================================================
       توجيه التصدير حسب نوع قاعدة البيانات
       ============================================================ */
    protected function dumpDatabase(string $outputFile, string $driver): void
    {
        match ($driver) {
            'mysql' => $this->dumpMysql($outputFile),
            'pgsql' => $this->dumpPostgres($outputFile),
            default => throw new \RuntimeException(
                "نوع قاعدة البيانات '{$driver}' غير مدعوم. المدعوم: mysql, pgsql"
            ),
        };
    }

    /* ============================================================
       MySQL — mysqldump
       ============================================================ */
    protected function dumpMysql(string $outputFile): void
    {
        $db = $this->getDbConfig();

        $host     = $db['host']     ?? '127.0.0.1';
        $port     = $db['port']     ?? '3306';
        $database = $db['database'] ?? '';
        $username = $db['username'] ?? '';
        $password = $db['password'] ?? '';

        if (!$database || !$username) {
            throw new \RuntimeException('إعدادات قاعدة البيانات غير مكتملة');
        }

        $binary = $this->findBinary('mysql');

        $args = [
            $binary,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            '--single-transaction',
            '--routines',
            '--triggers',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            // بعض إصدارات MySQL 8 ترفض هذا الخيار — نتجاهله بصمت
            '--column-statistics=0',
            $database,
        ];

        if ($password !== '') {
            $args[] = "--password={$password}";
        }

        $process = new Process($args);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'mysqldump failed: ' . $process->getErrorOutput()
            );
        }

        $output = $process->getOutput();
        if (empty($output)) {
            throw new \RuntimeException('mysqldump أنتج مخرجات فارغة');
        }

        File::put($outputFile, $output);
    }

    /* ============================================================
       PostgreSQL — pg_dump
       ============================================================
       ملاحظات:
       - pg_dump لا يقبل --password؛ نمرّره عبر متغير PGPASSWORD
       - نستخدم --no-owner --no-acl لتسهيل الاستعادة على سيرفر آخر
       - الصيغة: plain SQL (نص عادي) لسهولة القراءة والاستعادة
       ============================================================ */
    protected function dumpPostgres(string $outputFile): void
    {
        $db = $this->getDbConfig();

        $host     = $db['host']     ?? '127.0.0.1';
        $port     = $db['port']     ?? '5432';
        $database = $db['database'] ?? '';
        $username = $db['username'] ?? '';
        $password = $db['password'] ?? '';

        if (!$database || !$username) {
            throw new \RuntimeException('إعدادات قاعدة البيانات غير مكتملة');
        }

        $binary = $this->findBinary('pgsql');

        $args = [
            $binary,
            '--host=' . $host,
            '--port=' . $port,
            '--username=' . $username,
            '--no-owner',
            '--no-acl',
            '--clean',
            '--if-exists',
            '--format=plain',
            '--encoding=UTF8',
            $database,
        ];

        $process = new Process($args);

        // ✅ كلمة المرور تُمرَّر كمتغير بيئة (الممارسة القياسية لـ pg_dump)
        if ($password !== '') {
            $process->setEnv(['PGPASSWORD' => $password]);
        }

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                'pg_dump failed: ' . $process->getErrorOutput()
            );
        }

        $output = $process->getOutput();
        if (empty($output)) {
            throw new \RuntimeException('pg_dump أنتج مخرجات فارغة');
        }

        File::put($outputFile, $output);
    }

    /* ============================================================
       البحث عن المسار التنفيذي
       ============================================================ */
    protected function findBinary(string $driver): string
    {
        $config = self::SUPPORTED_DRIVERS[$driver] ?? null;
        if (!$config) {
            throw new \RuntimeException("السائق '{$driver}' غير مدعوم");
        }

        // 1) من .env
        $custom = env($config['env_key']);
        if ($custom && File::exists($custom)) {
            return $custom;
        }

        // 2) مسارات شائعة لكل نظام
        $candidates = $this->getBinaryCandidates($driver);

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        // 3) البحث في PATH عبر where/which
        $finder = new Process(
            PHP_OS_FAMILY === 'Windows'
                ? ['where', $config['binary']]
                : ['which', $config['binary']]
        );
        $finder->run();

        if ($finder->isSuccessful()) {
            $found = trim(explode("\n", trim($finder->getOutput()))[0]);
            if ($found && File::exists($found)) {
                return $found;
            }
        }

        throw new \RuntimeException(
            "لم يتم العثور على {$config['binary']}. " .
            "الرجاء إضافة {$config['env_key']} في ملف .env"
        );
    }

    /* ============================================================
       مسارات شائعة حسب النظام
       ============================================================ */
    protected function getBinaryCandidates(string $driver): array
    {
        if ($driver === 'mysql') {
            return [
                // Laragon (Windows)
                'C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe',
                'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe',
                'C:/laragon/bin/mysql/mysql-8.0.31-winx64/bin/mysqldump.exe',
                // XAMPP / WAMP
                'C:/xampp/mysql/bin/mysqldump.exe',
                'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
                'C:/wamp64/bin/mysql/mysql8.0.32/bin/mysqldump.exe',
                // Linux / macOS
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/opt/homebrew/bin/mysqldump',
                '/opt/homebrew/opt/mysql-client/bin/mysqldump',
            ];
        }

        // PostgreSQL
        return [
            // Windows (Laragon / standalone installer)
            'C:/laragon/bin/postgresql/bin/pg_dump.exe',
            'C:/laragon/bin/postgresql/pgsql/bin/pg_dump.exe',
            'C:/Program Files/PostgreSQL/16/bin/pg_dump.exe',
            'C:/Program Files/PostgreSQL/15/bin/pg_dump.exe',
            'C:/Program Files/PostgreSQL/14/bin/pg_dump.exe',
            // Linux / macOS
            '/usr/bin/pg_dump',
            '/usr/local/bin/pg_dump',
            '/usr/lib/postgresql/16/bin/pg_dump',
            '/usr/lib/postgresql/15/bin/pg_dump',
            '/usr/lib/postgresql/14/bin/pg_dump',
            '/opt/homebrew/bin/pg_dump',
            '/opt/homebrew/opt/postgresql@16/bin/pg_dump',
            '/Applications/Postgres.app/Contents/Versions/latest/bin/pg_dump',
        ];
    }

    /* ============================================================
       قراءة معلومات الاتصال الحالية
       ============================================================ */
    protected function getDriver(): string
    {
        $connection = config('database.default');
        return config("database.connections.{$connection}.driver", 'mysql');
    }

    protected function getDatabaseName(): string
    {
        return (string) $this->getDbConfig('database');
    }

    protected function getDbConfig(?string $key = null): mixed
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        return $key === null ? $config : ($config[$key] ?? null);
    }

    /* ============================================================
       ضغط ملفات في ZIP
       ============================================================ */
    protected function zipFiles(array $files, string $outputZip): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($outputZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('فشل فتح ملف ZIP للكتابة');
        }

        foreach ($files as $file) {
            if (File::exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }

        $zip->close();
    }

    /* ============================================================
       قائمة النسخ
       ============================================================ */
    public function list(): array
    {
        $files = File::glob($this->backupDir . '/backup_*.zip');
        $backups = [];

        foreach ($files as $file) {
            $name = basename($file);
            $backups[] = [
                'filename'   => $name,
                'size'       => File::size($file),
                'size_human' => $this->humanSize(File::size($file)),
                'created_at' => date('Y-m-d H:i:s', File::lastModified($file)),
                'age_days'   => (int) ((time() - File::lastModified($file)) / 86400),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /* ============================================================
       حذف نسخة
       ============================================================ */
    public function delete(string $filename): bool
    {
        $filename = basename($filename); // منع path traversal
        $path = "{$this->backupDir}/{$filename}";

        if (!File::exists($path) || !str_starts_with($filename, 'backup_')) {
            return false;
        }

        return File::delete($path);
    }

    /* ============================================================
       مسار ملف للتنزيل
       ============================================================ */
    public function getPath(string $filename): ?string
    {
        $filename = basename($filename);
        $path = "{$this->backupDir}/{$filename}";

        if (!File::exists($path) || !str_starts_with($filename, 'backup_')) {
            return null;
        }

        return $path;
    }

    /* ============================================================
       تنظيف النسخ القديمة
       ============================================================ */
    public function cleanOldBackups(int $keepDays = 30): int
    {
        $deleted = 0;
        $cutoff = time() - ($keepDays * 86400);
        $files = File::glob($this->backupDir . '/backup_*.zip');

        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoff) {
                if (File::delete($file)) $deleted++;
            }
        }

        return $deleted;
    }

    /* ============================================================
       Human-readable size
       ============================================================ */
    protected function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}