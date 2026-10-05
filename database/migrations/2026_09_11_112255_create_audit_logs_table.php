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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // من فعلها؟
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name')->nullable();       // snapshot للاسم
            $table->string('user_role', 50)->nullable();

            // ماذا فعل؟
            $table->string('action', 50)->index();         // created, updated, deleted, login, logout, sale, refund, withdraw, ...
            $table->string('model_type', 100)->nullable()->index();  // Sale, User, Medicine, ...
            $table->unsignedBigInteger('model_id')->nullable()->index();

            // التفاصيل
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // السياق
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('shift_id')->nullable()->index();
            $table->string('severity', 20)->default('info')->index(); // info, warning, critical

            $table->timestamps();

            $table->index(['created_at', 'action']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
