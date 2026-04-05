<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog_service_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_catalog_service_id');
            $table->string('name');
            $table->unsignedInteger('sort_order');
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->string('technician_report_label')->default('Relato tecnico');
            $table->boolean('requires_image_proof')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('service_catalog_service_id', 'service_catalog_steps_service_id_foreign')
                ->references('id')
                ->on('service_catalog_services')
                ->onDelete('cascade');
            $table->index(['service_catalog_service_id', 'sort_order'], 'service_catalog_steps_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog_service_steps');
    }
};

