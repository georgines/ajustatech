<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_equipment_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name', 'soet_name_idx');
            $table->index(['is_active', 'created_at'], 'soet_active_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_equipment_types');
    }
};
