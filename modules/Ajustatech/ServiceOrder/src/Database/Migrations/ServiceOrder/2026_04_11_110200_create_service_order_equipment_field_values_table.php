<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_equipment_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('equipment_type_field_id')->nullable();
            $table->string('field_type', 30);
            $table->string('field_label', 150);
            $table->string('field_placeholder', 180)->nullable();
            $table->boolean('is_required')->default(false);
            $table->text('value_text')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id', 'soefv_order_fk')
                ->references('id')
                ->on('service_order_bases')
                ->onDelete('cascade');

            $table->foreign('equipment_type_field_id', 'soefv_eq_field_fk')
                ->references('id')
                ->on('service_order_equipment_type_fields')
                ->nullOnDelete();

            $table->index(['service_order_id', 'field_label'], 'soefv_order_label_idx');
            $table->index('equipment_type_field_id', 'soefv_eq_field_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_equipment_field_values');
    }
};
