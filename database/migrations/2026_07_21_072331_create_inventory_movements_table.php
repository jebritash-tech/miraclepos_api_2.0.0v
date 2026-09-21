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
        Schema::create('inventory_movements', function (Blueprint $table) {
             $table->id();

            $table->foreignId('medicine_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('batch_id')
                ->nullable()
                ->constrained('medicine_batches')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [

                'purchase',

                'sale',

                'purchase_return',

                'sale_return',

                'adjustment',

                'transfer_in',

                'transfer_out'

            ]);

            // موجب عند الزيادة وسالب عند النقص
            $table->integer('quantity');

            // الرصيد بعد تنفيذ الحركة
            $table->integer('balance_after');

            // نوع المرجع (Purchase, Sale ...)
            $table->string('reference_type')->nullable();

            // رقم المرجع
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
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
        Schema::dropIfExists('inventory_movements');
    }
};
