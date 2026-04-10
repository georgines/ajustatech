<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_equipment_type_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_type_id');
            $table->string('field_type', 30);
            $table->string('label', 150);
            $table->string('placeholder', 180)->nullable();
            $table->text('default_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('disk', 50)->nullable();
            $table->text('path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('equipment_type_id', 'soetf_eq_type_fk')
                ->references('id')
                ->on('service_order_equipment_types')
                ->onDelete('cascade');

            $table->index(['equipment_type_id', 'field_type'], 'soetf_eqtype_ftype_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_equipment_type_fields');
    }
};
