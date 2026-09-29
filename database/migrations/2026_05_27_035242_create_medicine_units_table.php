<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medicine_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medicine_id')
                ->constrained()->unique()
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnDelete();
                

            /*
             * عدد وحدات الأساس
             */

            $table->integer('factor');

            /*
             * باركود مستقل
             */

            $table->string('barcode')->nullable();

            /*
             * هل هذه هي أصغر وحدة؟
             */

            $table->boolean('is_base')->default(false);

            /*
             * ترتيب العرض
             */

            $table->integer('sort_order')->default(1);
            $table->boolean('allow_sale')->default(true);
            $table->unique([
                    'medicine_id',
                    'unit_id'
                ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_units');
    }
};
