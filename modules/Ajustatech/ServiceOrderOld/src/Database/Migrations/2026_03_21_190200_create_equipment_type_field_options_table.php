<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_type_field_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_type_field_id');
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('equipment_type_field_id')->references('id')->on('equipment_type_fields')->onDelete('cascade');
            $table->unique(['equipment_type_field_id', 'value'], 'equipment_type_field_options_value_unique');
            $table->index(['equipment_type_field_id', 'is_active', 'sort_order'], 'equipment_type_field_options_active_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_type_field_options');
    }
};

