<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_service_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('service_catalog_service_id')->nullable();
            $table->string('service_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->json('service_snapshot')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('service_catalog_service_id')->references('id')->on('service_catalog_services')->nullOnDelete();
            $table->index('service_order_id', 'service_order_service_items_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_service_items');
    }
};

