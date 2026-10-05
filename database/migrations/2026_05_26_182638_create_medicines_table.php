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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name')->index();
            $table->string('scientific_name')->nullable();
            $table->string('notes')->nullable();
            
            $table->enum('pricing_method',[
                'local',
                'imported'
            ])->default('local');

            $table->integer('strips_per_box')->default(1);

            $table->integer('pieces_per_strip')->default(1);

            $table->boolean('allow_box_sale')->default(true);

            $table->boolean('allow_strip_sale')->default(true);

            $table->boolean('allow_piece_sale')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
