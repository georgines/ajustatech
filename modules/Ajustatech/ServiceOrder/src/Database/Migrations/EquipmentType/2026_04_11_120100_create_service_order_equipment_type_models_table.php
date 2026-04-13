<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_equipment_type_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('equipment_type_brand_id');
            $table->string('name', 120);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('equipment_type_brand_id', 'soetm_brand_fk')
                ->references('id')
                ->on('service_order_equipment_type_brands')
                ->cascadeOnDelete();

            $table->unique(['equipment_type_brand_id', 'name'], 'soetm_brand_name_unique');
            $table->index(['equipment_type_brand_id', 'usage_count'], 'soetm_brand_usage_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_equipment_type_models');
    }
};
