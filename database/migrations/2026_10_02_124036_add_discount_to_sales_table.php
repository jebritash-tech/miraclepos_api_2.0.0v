<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // نوع الخصم: fixed (مبلغ ثابت) أو percent (نسبة)
            $table->enum('discount_type', ['fixed', 'percent'])
                ->nullable()
                ->after('profit_amount');

            // القيمة المُدخلة (مثلاً 500 أو 10%)
            $table->decimal('discount_value', 12, 2)
                ->default(0)
                ->after('discount_type');

            // المبلغ الفعلي المخصوم (محسوب)
            $table->decimal('discount_amount', 12, 2)
                ->default(0)
                ->after('discount_value');

            // سبب الخصم (اختياري)
            $table->string('discount_reason', 255)
                ->nullable()
                ->after('discount_amount');

            // معرف الموظف الذي طبّق الخصم (للمراقبة)
            $table->foreignId('discount_by')
                ->nullable()
                ->after('discount_reason')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['discount_by']);
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_amount',
                'discount_reason',
                'discount_by',
            ]);
        });
    }
};