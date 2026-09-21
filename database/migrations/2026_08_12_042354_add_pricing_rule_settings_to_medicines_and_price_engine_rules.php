<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Link Medicine To Pricing Rule
        |--------------------------------------------------------------------------
        */

        Schema::table('medicines', function (Blueprint $table) {

            $table->foreignId('pricing_rule_id')
                ->nullable()
                ->after('id')
                ->constrained('price_engine_rules')
                ->nullOnDelete();

        });

        /*
        |--------------------------------------------------------------------------
        | Pricing Rule Settings
        |--------------------------------------------------------------------------
        */

        Schema::table('price_engine_rules', function (Blueprint $table) {

            $table->boolean('is_default')
                ->default(false)
                ->after('is_active');

            $table->json('settings')
                ->nullable()
                ->after('is_default');

        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {

            $table->dropForeign([
                'pricing_rule_id'
            ]);

            $table->dropColumn(
                'pricing_rule_id'
            );

        });

        Schema::table('price_engine_rules', function (Blueprint $table) {

            $table->dropColumn([
                'is_default',
                'settings'
            ]);

        });
    }
};