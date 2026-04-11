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
            $table->uuid('procedure_id')->nullable();
            $table->string('item_name', 150);
            $table->text('item_notes')->nullable();
            $table->decimal('unit_value', 12, 2)->default(0);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_order_id', 'sosi_order_fk')
                ->references('id')
                ->on('service_order_bases')
                ->onDelete('cascade');

            $table->foreign('procedure_id', 'sosi_procedure_fk')
                ->references('id')
                ->on('service_order_procedures')
                ->nullOnDelete();

            $table->index(['service_order_id', 'sort_order'], 'sosi_order_sort_idx');
            $table->index('procedure_id', 'sosi_procedure_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_service_items');
    }
};
