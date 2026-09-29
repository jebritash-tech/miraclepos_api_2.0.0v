<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // ✅ بعد total_amount مباشرة
            $table->decimal('subtotal', 15, 2)
                ->default(0)
                ->after('discount');
        });

        // ✅ ترحيل البيانات: subtotal = total_amount + discount
        // (للفواتير القديمة، subtotal كان يساوي total_amount)
        DB::statement('
            UPDATE purchases 
            SET subtotal = total_amount + COALESCE(discount, 0)
        ');
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
};