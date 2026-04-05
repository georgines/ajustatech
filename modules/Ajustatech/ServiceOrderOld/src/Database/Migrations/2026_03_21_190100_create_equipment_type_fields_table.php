<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_type_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_type_id');
            $table->string('field_type');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_printable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->foreign('equipment_type_id')->references('id')->on('equipment_types')->onDelete('cascade');
            $table->unique(['equipment_type_id', 'slug'], 'equipment_type_fields_type_slug_unique');
            $table->index(['equipment_type_id', 'is_active', 'sort_order'], 'equipment_type_fields_active_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_type_fields');
    }
};

