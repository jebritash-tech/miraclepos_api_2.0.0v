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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medicine_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity')
                ->default(0);
            $table->decimal('purchased_quantity', 10, 2)->default(0);
            $table->decimal('factor', 10, 2)->default(1);
            $table->integer('reserved_quantity')
                ->default(0);

            $table->integer('available_quantity')
                ->default(0);

            $table->integer('minimum_quantity')
                ->default(1000);

            $table->integer('maximum_quantity')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'medicine_id',
                'branch_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
