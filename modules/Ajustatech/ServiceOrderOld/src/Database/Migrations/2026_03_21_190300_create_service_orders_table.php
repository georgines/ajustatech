<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_type_id');
            $table->string('customer_name');
            $table->string('equipment_name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('entry_date')->nullable();
            $table->text('reported_issue')->nullable();
            $table->string('status')->default('open');
            $table->json('equipment_type_snapshot');
            $table->json('fields_snapshot');
            $table->timestamps();

            $table->foreign('equipment_type_id')->references('id')->on('equipment_types');
            $table->index(['equipment_type_id', 'status'], 'service_orders_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};

