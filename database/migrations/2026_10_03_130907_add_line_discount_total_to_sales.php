<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            /*
            |----------------------------------------------------------
            | line_discount_total — مجموع خصومات البنود المباشرة
            |----------------------------------------------------------
            | = sum(sale_items.line_discount)
            |
            | ملاحظة: هذا حقل مُشتق يُخزَّن للتقارير السريعة فقط.
            |           القيمة الحقيقية موجودة في sale_items.
            */
            $table->decimal('line_discount_total', 12, 2)
                  ->default(0)
                  ->after('discount_amount');

            /*
            |----------------------------------------------------------
            | discount_scope — نطاق الخصم
            |----------------------------------------------------------
            | invoice : خصم على الفاتورة كاملة فقط
            | items   : خصم على بنود محددة فقط
            | mixed   : كلاهما معاً
            | none    : لا يوجد خصم
            */
            $table->string('discount_scope', 20)
                  ->default('none')
                  ->after('line_discount_total');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['line_discount_total', 'discount_scope']);
        });
    }
};