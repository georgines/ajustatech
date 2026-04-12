<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('company_hours_working_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_hour_id');
            $table->string('day_key', 20);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();

            $table->foreign('company_hour_id', 'chwd_company_hour_fk')
                ->references('id')
                ->on('company_hours')
                ->cascadeOnDelete();
            $table->unique(['company_hour_id', 'day_key'], 'chwd_company_day_uidx');
            $table->index(['company_hour_id', 'sort_order'], 'chwd_company_sort_idx');
        });

        Schema::create('company_hours_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_hour_id');
            $table->string('holiday_name', 120);
            $table->date('holiday_date');
            $table->timestamps();

            $table->foreign('company_hour_id', 'chh_company_hour_fk')
                ->references('id')
                ->on('company_hours')
                ->cascadeOnDelete();
            $table->unique(['company_hour_id', 'holiday_date'], 'chh_company_date_uidx');
            $table->index(['company_hour_id', 'holiday_date'], 'chh_company_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_hours_holidays');
        Schema::dropIfExists('company_hours_working_days');
        Schema::dropIfExists('company_hours');
    }
};
