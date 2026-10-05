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
        Schema::create('shifts', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('opening_cash',15,2);

            $table->decimal('expected_cash',15,2)
                ->default(0);

            $table->decimal('closing_cash',15,2)
                ->nullable();

            $table->decimal('cash_sales',15,2)
                ->default(0);

            $table->decimal('card_sales',15,2)
                ->default(0);

            $table->decimal('refund_amount',15,2)
                ->default(0);

            $table->decimal('expenses_amount',15,2)
                ->default(0);

            $table->decimal('debts_amount',15,2)
                ->default(0);

            $table->decimal('withdraw_amount',15,2)
                ->default(0);

            $table->integer('sales_count')
                ->default(0);

            $table->enum('status',[
                'open',
                'closed'
            ])->default('open');

            $table->timestamp('opened_at');

            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
