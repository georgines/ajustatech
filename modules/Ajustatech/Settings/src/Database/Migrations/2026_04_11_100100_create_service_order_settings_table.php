<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('initial_order_number');
            $table->json('working_days_json');
            $table->json('holidays_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_settings');
    }
};


