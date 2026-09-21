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
        Schema::create('medicine_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('batch_id')
                ->constrained('medicine_batches')
                ->cascadeOnDelete();

            $table->foreignId('medicine_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Prices
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'buy_price',
                14,
                2
            );

            $table->decimal(
                'sell_price',
                14,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Profit
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'profit_amount',
                14,
                2
            )->default(0);

            $table->decimal(
                'profit_percent',
                8,
                2
            )->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([

                'batch_id',

                'unit_id'

            ]);

            $table->enum('price_mode', ['auto', 'manual'])
                ->default('auto');
                

            $table->foreignId('locked_by')
                ->nullable()
               
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('locked_at')->nullable();

            $table->string('lock_reason', 255)
                ->nullable();
                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_prices');
    }
};
