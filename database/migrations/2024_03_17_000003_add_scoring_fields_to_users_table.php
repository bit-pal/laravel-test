<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fund_type')->nullable();
            $table->string('fund_manager')->nullable();
            $table->json('geo_preferences')->nullable();
            $table->json('sector_preferences')->nullable();
            $table->json('company_stage_preferences')->nullable();
            $table->boolean('global_preference')->default(false);
            $table->boolean('agnostic_preference')->default(false);
            $table->integer('weekly_booking_limit')->default(2);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fund_type',
                'fund_manager',
                'geo_preferences',
                'sector_preferences',
                'company_stage_preferences',
                'global_preference',
                'agnostic_preference',
                'weekly_booking_limit'
            ]);
        });
    }
}; 