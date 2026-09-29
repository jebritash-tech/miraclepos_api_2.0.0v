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
        Schema::create('shift_activities', function (Blueprint $table) {
                $table->id();

                $table->foreignId('shift_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('type',40);

                $table->decimal('amount',12,2)->default(0);

                $table->string('reference_type')->nullable();

                $table->unsignedBigInteger('reference_id')->nullable();

                $table->string('title');

                $table->text('description')->nullable();

                $table->json('meta')->nullable();

                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_activities');
    }
};
