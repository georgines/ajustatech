<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_cash_flow_routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('flow_key');
            $table->string('payment_method_type')->nullable();
            $table->uuid('company_cash_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('company_cash_id')->references('id')->on('company_cashes')->onDelete('restrict');
            $table->index(['flow_key', 'payment_method_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_cash_flow_routes');
    }
};