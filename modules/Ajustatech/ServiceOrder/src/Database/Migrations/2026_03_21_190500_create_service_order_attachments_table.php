<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('equipment_type_field_id')->nullable();
            $table->string('field_slug');
            $table->string('attachment_type');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('equipment_type_field_id')->references('id')->on('equipment_type_fields')->nullOnDelete();
            $table->index(['service_order_id', 'field_slug'], 'service_order_attachments_slug_index');
            $table->index(['service_order_id', 'attachment_type'], 'service_order_attachments_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_attachments');
    }
};

