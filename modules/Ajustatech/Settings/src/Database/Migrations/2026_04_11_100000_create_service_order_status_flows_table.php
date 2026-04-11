<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_status_flows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 80);
            $table->string('name', 120);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_default_initial')->default(false);
            $table->timestamps();

            $table->unique('code', 'sosf_code_uidx');
            $table->index('sort_order', 'sosf_sort_order_idx');
            $table->index('is_default_initial', 'sosf_default_initial_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_status_flows');
    }
};


