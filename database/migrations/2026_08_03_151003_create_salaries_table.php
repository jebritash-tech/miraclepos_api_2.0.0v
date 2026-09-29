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
        Schema::create('salaries', function (Blueprint $table) {
           $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('month');

            $table->unsignedSmallInteger('year');

            $table->decimal('basic_salary',12,2);

            $table->decimal('allowances',12,2)
                ->default(0);

            $table->decimal('deductions',12,2)
                ->default(0);

            $table->decimal('net_salary',12,2);

            $table->enum('status',[

                'pending',

                'paid'

            ])->default('pending');

            $table->date('paid_at')->nullable();

            $table->enum('payment_method',[

                'cash',

                'bank'

            ])->nullable();

            $table->string('bank_name')->nullable();

            $table->string('bank_reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([

                'user_id',

                'month',

                'year'

            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
