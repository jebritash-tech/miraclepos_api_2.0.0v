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
        Schema::create('price_engine_rules', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->enum('type', [

                'percentage',

                'fixed',

                'multiply'

            ]);

            $table->enum('apply_on', [

                'buy_price',

                'sell_price',

                'profit'

            ]);

            $table->decimal('value',12,2);

            $table->integer('sort_order')->default(1);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_engine_rules');
    }
};
