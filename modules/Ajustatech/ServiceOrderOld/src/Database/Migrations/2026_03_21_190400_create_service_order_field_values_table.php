<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('equipment_type_field_id')->nullable();
            $table->string('field_slug');
            $table->string('field_type');
            $table->longText('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->json('field_snapshot')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('equipment_type_field_id')->references('id')->on('equipment_type_fields')->nullOnDelete();
            $table->unique(['service_order_id', 'field_slug'], 'service_order_field_values_slug_unique');
            $table->index(['service_order_id', 'field_type'], 'service_order_field_values_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_field_values');
    }
};

