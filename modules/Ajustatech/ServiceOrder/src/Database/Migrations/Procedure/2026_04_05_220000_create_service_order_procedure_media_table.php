<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_procedure_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('procedure_id');
            $table->string('type', 20);
            $table->text('url')->nullable();
            $table->string('disk', 50)->nullable();
            $table->text('path')->nullable();
            $table->string('original_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('procedure_id', 'sopm_procedure_fk')
                ->references('id')
                ->on('service_order_procedures')
                ->onDelete('cascade');

            $table->index(['procedure_id', 'type'], 'sopm_procedure_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_procedure_media');
    }
};

