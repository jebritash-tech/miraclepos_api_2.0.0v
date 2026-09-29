<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected BackupService $service;

    public function __construct(BackupService $service)
    {
        $this->service = $service;
    }

    /* ============================================================
       GET /api/backups
       ============================================================ */
    public function index()
    {
        return response()->json([
            'backups' => $this->service->list(),
            'total_disk_usage' => $this->totalSize(),
            'auto_backup_info' => [
                'enabled'  => true,
                'schedule' => 'كل يوم الساعة 23:59',
                'keep_days'=> 30,
            ],
        ]);
    }

    /* ============================================================
       POST /api/backups/create
       ============================================================ */
    public function create(Request $request)
    {
        try {
            $result = $this->service->create('manual');

            return response()->json([
                'message' => 'تم إنشاء النسخة الاحتياطية بنجاح',
                'backup'  => [
                    'filename'   => $result['file'],
                    'size'       => $result['size'],
                    'size_human' => $this->humanSize($result['size']),
                    'created_at' => $result['created_at'],
                ],
                'deleted_old' => $result['deleted_old'] ?? 0,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Backup create error: ' . $e->getMessage());
            return response()->json([
                'message' => 'تعذر إنشاء النسخة الاحتياطية: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ============================================================
       GET /api/backups/{filename}/download
       ============================================================ */
    public function download(string $filename)
    {
        $path = $this->service->getPath($filename);
        if (!$path) {
            return response()->json(['message' => 'النسخة غير موجودة'], 404);
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

        /* ============================================================
       POST /api/backups/{filename}/restore
       ============================================================
       ⚠️ عملية خطيرة — تستبدل كل البيانات الحالية
       ============================================================ */
    public function restore(Request $request, string $filename)
    {
        // ✅ تحقق أوضح
        $filename = basename($filename);

        if (empty($filename) || $filename === 'undefined') {
            return response()->json([
                'message' => 'لم يتم تحديد اسم الملف بشكل صحيح'
            ], 400);
        }

        if (!str_starts_with($filename, 'backup_')) {
            return response()->json([
                'message' => 'اسم ملف غير صالح — يجب أن يبدأ بـ backup_',
                'received' => $filename,
            ], 400);
        }

        // تحقق من وجود الملف
        if (!$this->service->getPath($filename)) {
            return response()->json([
                'message' => "الملف غير موجود: {$filename}"
            ], 404);
        }

        /* ... باقي الكود كما هو ... */
    }

    /* ============================================================
       DELETE /api/backups/{filename}
       ============================================================ */
    public function destroy(string $filename)
    {
        $deleted = $this->service->delete($filename);
        if (!$deleted) {
            return response()->json(['message' => 'النسخة غير موجودة'], 404);
        }
        return response()->json(['message' => 'تم حذف النسخة']);
    }

    /* ============================================================
       Helpers
       ============================================================ */
    protected function totalSize(): array
    {
        $bytes = 0;
        foreach ($this->service->list() as $b) {
            $bytes += $b['size'];
        }
        return [
            'bytes' => $bytes,
            'human' => $this->humanSize($bytes),
        ];
    }

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