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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_batch_id')->constrained(); // الربط بالدفعة ضروري
            $table->integer('quantity');
            $table->enum(
                'unit',

                [

                'box',

                'strip',

                'piece'

                ]

            );
            $table->integer('quantity_pieces')->default(0);
            $table->foreignId('medicine_unit_id')->nullable()->default(0);
            $table->integer('quantity_base')->default(0);
            $table->decimal('price', 10, 2); // السعر وقت البيع
            $table->decimal('profit', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
