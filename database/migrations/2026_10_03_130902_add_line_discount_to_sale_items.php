<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            /*
            |----------------------------------------------------------
            | line_discount — الخصم المباشر على البند
            |----------------------------------------------------------
            | يُملأ فقط عندما يحدد الكاشير خصماً مباشراً على البند.
            | يبقى 0 عندما لا يوجد خصم مباشر على البند.
            */
            $table->decimal('line_discount', 12, 2)
                  ->default(0)
                  ->after('profit');

            /*
            |----------------------------------------------------------
            | invoice_share — نصيب البند من خصم الفاتورة
            |----------------------------------------------------------
            | عندما يُطبَّق خصم على مستوى الفاتورة كاملة،
            | يُوزَّع نسبياً على كل بند ويُخزَّن هنا.
            */
            $table->decimal('invoice_share', 12, 2)
                  ->default(0)
                  ->after('line_discount');

            /*
            |----------------------------------------------------------
            | effective_total — ما دفعه العميل فعلاً لهذا البند
            |----------------------------------------------------------
            | effective_total = (price × quantity) - line_discount - invoice_share
            |
            | ⚠️ الضمانة الحسابية:
            |    sum(effective_total) === sale.total_amount (بالضبط)
            */
            $table->decimal('effective_total', 12, 2)
                  ->default(0)
                  ->after('invoice_share');

            // فهرس للبحث السريع عند الإرجاع
            $table->index(['sale_id', 'effective_total'], 'idx_sale_effective');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('idx_sale_effective');
            $table->dropColumn(['line_discount', 'invoice_share', 'effective_total']);
        });
    }
};