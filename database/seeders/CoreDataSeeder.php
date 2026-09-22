<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Setting;

/**
 * ═══════════════════════════════════════════════════════════════════
 * CoreDataSeeder — البيانات الأساسية الثابتة
 * ═══════════════════════════════════════════════════════════════════
 * 
 * هذا الـ Seeder يُنشئ فقط:
 *   ✅ الفروع (Branches)
 *   ✅ الوحدات (Units)
 *   ✅ التصنيفات (Categories)
 *   ✅ المستخدمين الأساسيين (Admin + Cashier)
 *   ✅ قواعد التسعير (PriceEngineRules)
 *   ✅ الإعدادات (Settings)
 * 
 * لا يُنشئ:
 *   ❌ أدوية / مشتريات / دفعات / أسعار
 *   ❌ أي بيانات تجارية
 * 
 * ✅ آمن للتشغيل في الإنتاج
 * ✅ idempotent (يمكن تشغيله أكثر من مرة بأمان)
 * 
 * التشغيل:
 *   php artisan db:seed --class=CoreDataSeeder
 * 
 * أو كجزء من:
 *   php artisan migrate:fresh --seed
 * ═══════════════════════════════════════════════════════════════════
 */
class CoreDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 بدء CoreDataSeeder...');

        DB::transaction(function () {
            $this->seedBranches();
            $this->seedUnits();
            $this->seedCategories();
            $this->seedUsers();
            $this->seedPriceEngineRules();
            $this->seedSettings();
        });

        $this->command->info('✅ اكتمل CoreDataSeeder بنجاح.');
        $this->command->newLine();
        $this->command->info('📋 بيانات الدخول:');
        $this->command->line('   👤 Admin:   admin@miraclepos.test / password');
        $this->command->line('   👤 Cashier: cashier@miraclepos.test / password');
        $this->command->newLine();
    }

    /* ============================================================
       الفروع — فرع رئيسي واحد
       ============================================================ */
    private function seedBranches(): void
    {
        $branches = [
            [
                'name'     => 'الفرع الرئيسي',
                'location' => 'السودان',
            ],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['name' => $branch['name']],
                array_merge($branch, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->line('   ✅ ' . count($branches) . ' فرع');
    }

            /* ============================================================
       الوحدات (Units)
       ============================================================
       
       ⚠️ هذه الوحدات يجب أن تطابق UNIT_MAP في MedicinesSeeder.
       
       ملاحظة مهمة:
       ─────────────────────────────────────────────────────────────
       القيم مثل "box-strip" في ملف Excel لا تعني وحدة واحدة،
       بل تعني أن الدواء يُباع بإحدى وحدتين: علبة أو شريط.
       تُفكَّك هذه القيم في MedicinesSeeder تلقائياً.
       ============================================================ */
    private function seedUnits(): void
    {
        $units = [
            // ═══════════ وحدات التعبئة الأساسية ═══════════
            ['name' => 'علبة',              'symbol' => 'BOX',      'active' => 1],
            ['name' => 'شريط',              'symbol' => 'STRIP',    'active' => 1],
            ['name' => 'قرص',               'symbol' => 'TAB',      'active' => 1],
            ['name' => 'قطعة',              'symbol' => 'PIC',      'active' => 1],
            ['name' => 'حبة',               'symbol' => 'PIECE',    'active' => 1],
            ['name' => 'كبسولة',            'symbol' => 'CAP',      'active' => 1],

            // ═══════════ وحدات السوائل ═══════════
            ['name' => 'قارورة',            'symbol' => 'BOTTLE',   'active' => 1],
            ['name' => 'أنبوبة',            'symbol' => 'TUBE',     'active' => 1],
            ['name' => 'مرهم',              'symbol' => 'TUB',      'active' => 1],
            ['name' => 'فيال',              'symbol' => 'VIAL',     'active' => 1],
            ['name' => 'أمبول',             'symbol' => 'AMP',      'active' => 1],
            ['name' => 'بخة',               'symbol' => 'SPRAY',    'active' => 1],
            ['name' => 'كيس',               'symbol' => 'SACHET',   'active' => 1],
            ['name' => 'مل',                'symbol' => 'ML',       'active' => 1],
            ['name' => 'جرام',              'symbol' => 'G',        'active' => 1],

            // ═══════════ وحدات خاصة بالأشكال الدوائية ═══════════
            ['name' => 'قطرة',              'symbol' => 'DROP',     'active' => 1],
            ['name' => 'استنشاق',           'symbol' => 'INH',      'active' => 1],
            ['name' => 'محلول وريدي',       'symbol' => 'DRIP',     'active' => 1],
            ['name' => 'طقم',               'symbol' => 'SET',      'active' => 1],
            ['name' => 'تحميلة',            'symbol' => 'SUPP',     'active' => 1],
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['symbol' => $unit['symbol']],
                array_merge($unit, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->line('   ✅ ' . count($units) . ' وحدة');
    }
    /* ============================================================
       التصنيفات (Categories)
       ============================================================ */
    private function seedCategories(): void
    {
        $categories = [
            'مضادات حيوية',
            'مسكنات وخوافض حرارة',
            'أدوية الجهاز الهضمي',
            'أدوية القلب والضغط',
            'أدوية السكري',
            'أدوية الجهاز التنفسي',
            'أدوية الأطفال',
            'فيتامينات ومكملات',
            'كريمات ومراهم',
            'قطرات العين والأذن',
            'أدوية نفسية وعصبية',
            'أدوية الحساسية',
            'مستلزمات طبية',
            'أدوية العيون',
            'مطهرات ومعقمات',
            'أدوية الأسنان',
            'أدوية النساء والحمل',
            'أدوية المسالك البولية',
        ];

        foreach ($categories as $name) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                [
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->line('   ✅ ' . count($categories) . ' تصنيف');
    }

    /* ============================================================
       المستخدمون الأساسيون (Admin + Cashier)
       ============================================================ */
    private function seedUsers(): void
    {
        $branchId = DB::table('branches')->first()->id;

        // ═══════════════════════════════════════════════════════════
        // Admin
        // ═══════════════════════════════════════════════════════════
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@miraclepos.test'],
            [
                'branch_id'         => $branchId,
                'name'              => 'مدير النظام',
                'email'             => 'admin@miraclepos.test',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'salary'            => 500000.00,
                'is_active'         => 1,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        // ═══════════════════════════════════════════════════════════
        // Cashier
        // ═══════════════════════════════════════════════════════════
        DB::table('users')->updateOrInsert(
            ['email' => 'cashier@miraclepos.test'],
            [
                'branch_id'         => $branchId,
                'name'              => 'كاشير افتراضي',
                'email'             => 'cashier@miraclepos.test',
                'password'          => Hash::make('password'),
                'role'              => 'cashier',
                'salary'            => 250000.00,
                'is_active'         => 1,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $this->command->line('   ✅ 2 مستخدم (admin + cashier)');
    }

    /* ============================================================
       قواعد التسعير — مبنية على واقع الصيدليات السودانية
       ============================================================
       
       المنطق:
       ─────────────────────────────────────────────────────────────
       • القاعدة الافتراضية 40%   → لمعظم الأدوية المحلية العادية
       • المستوردة 55%            → لأن المستورد تكلفته أعلى ومخاطره أكبر
       • المضادات الحيوية 25%     → سوق تنافسي جداً + أسعار مراقبة
       • أدوية السكري 15%         → أدوية مزمنة، المنافسة شديدة
       
       التقريب:
       ─────────────────────────────────────────────────────────────
       • default  → أقرب 100 ج.س (لتسهيل التعامل النقدي)
       • imported → أقرب 500 ج.س (أسعار مرتفعة عادة)
       • antibiotics → أقرب 25 ج.س (أسعار منخفضة + دقيقة)
       • diabetes → أقرب 50 ج.س
       ============================================================ */
       /* ============================================================
       قواعد التسعير — مبنية على واقع الصيدليات السودانية
       ============================================================
       
       المنطق:
       ─────────────────────────────────────────────────────────────
       • القاعدة الافتراضية 40%   → لمعظم الأدوية المحلية العادية
       • المستوردة 55%            → لأن المستورد تكلفته أعلى
       • المضادات الحيوية 20%     → سوق تنافسي جداً
       • أدوية السكري 15%         → أدوية مزمنة، منافسة شديدة
       
       التقريب:
       ─────────────────────────────────────────────────────────────
       ✅ جميع القواعد تستخدم "التقريب المتدرج الذكي":
          - <50 ج.س     → 5
          - 50-500      → 25
          - 500-5000    → 100
          - >5000       → 500
       
       هذا يضمن أسعاراً منطقية لكل الأدوية بغض النظر عن السعر.
       ============================================================ */
    private function seedPriceEngineRules(): void
    {
        // ✅ إعدادات التقريب الموحدة (متدرج)
        $tieredRounding = json_encode([
            'rounding' => [
                'mode'   => 'up',
                'tiered' => true,
                'unit'   => 0,
            ],
        ], JSON_UNESCAPED_UNICODE);

        $rules = [
            [
                'name'       => 'الافتراضية — 40%',
                'type'       => 'percentage',
                'apply_on'   => 'buy_price',
                'value'      => 40.00,
                'sort_order' => 1,
                'is_active'  => 1,
                'is_default' => 1,
                'settings'   => $tieredRounding,
            ],
            [
                'name'       => 'الأدوية المستوردة — 55%',
                'type'       => 'percentage',
                'apply_on'   => 'buy_price',
                'value'      => 55.00,
                'sort_order' => 2,
                'is_active'  => 1,
                'is_default' => 0,
                'settings'   => $tieredRounding,
            ],
            [
                'name'       => 'المضادات الحيوية — 20%',
                'type'       => 'percentage',
                'apply_on'   => 'buy_price',
                'value'      => 20.00,
                'sort_order' => 3,
                'is_active'  => 1,
                'is_default' => 0,
                'settings'   => $tieredRounding,
            ],
            [
                'name'       => 'أدوية السكري — 15%',
                'type'       => 'percentage',
                'apply_on'   => 'buy_price',
                'value'      => 15.00,
                'sort_order' => 4,
                'is_active'  => 1,
                'is_default' => 0,
                'settings'   => $tieredRounding,
            ],
        ];

        foreach ($rules as $rule) {
            if ($rule['is_default'] === 1) {
                DB::table('price_engine_rules')
                    ->where('name', '!=', $rule['name'])
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }

            DB::table('price_engine_rules')->updateOrInsert(
                ['name' => $rule['name']],
                array_merge($rule, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->line('   ✅ ' . count($rules) . ' قاعدة تسعير (تقريب متدرج)');
    }

        /* ============================================================
       الإعدادات (Settings)
       ============================================================
       
       المجموعات:
       - pharmacy: معلومات الصيدلية
       - print:    إعدادات الطباعة
       - pin:      نظام PIN
       - security: إعدادات الأمان
       - backup:   النسخ الاحتياطي
       ============================================================ */
    private function seedSettings(): void
    {
        $settings = [
            /* ═══════════════════════════════════════════════════════
               Pharmacy Info
               ═══════════════════════════════════════════════════════ */
            ['pharmacy.name',        'MiraclePOS',         'string',  'pharmacy', 'اسم الصيدلية'],
            ['pharmacy.phone',       '',                    'string',  'pharmacy', 'الهاتف'],
            ['pharmacy.address',     '',                    'string',  'pharmacy', 'العنوان'],
            ['pharmacy.tax_number',  '',                    'string',  'pharmacy', 'الرقم الضريبي'],
            ['pharmacy.currency',    'ج.س',                 'string',  'pharmacy', 'العملة'],
            ['pharmacy.logo_url',    '',                    'string',  'pharmacy', 'شعار الصيدلية'],

            /* ═══════════════════════════════════════════════════════
               Print Settings
               ═══════════════════════════════════════════════════════ */
            ['print.enabled',           '1',    'boolean', 'print', 'تفعيل الطباعة'],
            ['print.auto_after_sale',   '1',    'boolean', 'print', 'طباعة تلقائية بعد البيع'],
            ['print.width',             '80',   'integer', 'print', 'عرض الطابعة (مم)'],
            ['print.allow_reprint',     '1',    'boolean', 'print', 'السماح بإعادة الطباعة'],
            ['print.auto_close_shift',  '1',    'boolean', 'print', 'طباعة تقرير إغلاق الوردية'],

            /* ═══════════════════════════════════════════════════════
               PIN
               ═══════════════════════════════════════════════════════ */
            ['pin.enabled',         '1', 'boolean', 'pin', 'تفعيل نظام PIN'],
            ['pin.length',          '4', 'integer', 'pin', 'طول PIN'],
            ['pin.max_attempts',    '5', 'integer', 'pin', 'الحد الأقصى للمحاولات'],
            ['pin.lockout_minutes', '5', 'integer', 'pin', 'مدة القفل (دقائق)'],

            /* PIN — متى يُطلب */
            ['pin.on_shift_open',   '1', 'boolean', 'pin', 'PIN عند فتح وردية'],
            ['pin.on_shift_close',  '1', 'boolean', 'pin', 'PIN عند إغلاق وردية'],
            ['pin.on_withdraw',     '1', 'boolean', 'pin', 'PIN عند السحب النقدي'],
            ['pin.on_void_sale',    '1', 'boolean', 'pin', 'PIN عند حذف فاتورة'],
            ['pin.on_price_change', '1', 'boolean', 'pin', 'PIN عند تغيير السعر'],
            ['pin.on_every_sale',   '0', 'boolean', 'pin', 'PIN عند كل عملية بيع'],
            ['pin.on_expense',      '1', 'boolean', 'pin', 'PIN عند إضافة مصروف'],
            ['pin.on_debt_payment', '1', 'boolean', 'pin', 'PIN عند سداد دين'],

            /* ═══════════════════════════════════════════════════════
               Security Settings (جديد ✅)
               ═══════════════════════════════════════════════════════ */
            [
                'security.session_timeout_minutes',
                '120',
                'integer',
                'security',
                'مدة انتهاء الجلسة (دقائق)',
            ],
            [
                'security.require_password_change_days',
                '0',
                'integer',
                'security',
                'إلزام تغيير كلمة المرور كل (أيام)',
            ],

            /* ═══════════════════════════════════════════════════════
               Backup Settings (جديد ✅)
               ═══════════════════════════════════════════════════════ */
            [
                'backup.auto_enabled',
                '1',
                'boolean',
                'backup',
                'نسخ احتياطي تلقائي',
            ],
            [
                'backup.keep_days',
                '30',
                'integer',
                'backup',
                'مدة الاحتفاظ بالنسخ',
            ],
        ];

        $inserted = 0;
        foreach ($settings as $s) {
            [$key, $value, $type, $group, $label] = $s;

            // updateOrInsert — لا يُعيد كتابة القيمة إن كانت موجودة
            $exists = DB::table('settings')->where('key', $key)->exists();

            if (!$exists) {
                DB::table('settings')->insert([
                    'key'         => $key,
                    'value'       => $value,
                    'type'        => $type,
                    'group'       => $group,
                    'label'       => $label,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $inserted++;
            }
        }

        // امسح Cache الإعدادات
        try {
            Setting::clearCache();
        } catch (\Throwable $e) {
            // لا نوقف إذا فشل Cache
        }

        $this->command->line("   ✅ {$inserted} إعداد جديد (المجموع: " . count($settings) . ")");
    }
}
