<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_cash_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('source_company_cash_id');
            $table->date('business_date');
            $table->decimal('opening_amount', 15, 2);
            $table->decimal('closing_amount', 15, 2)->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->string('status')->default('open');
            $table->string('outflow_status')->default('completed');
            $table->string('inflow_status')->default('pending');
            $table->timestamps();

            $table->foreign('source_company_cash_id')->references('id')->on('company_cashes')->onDelete('restrict');
            $table->index(['user_id', 'business_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_cash_sessions');
    }
};