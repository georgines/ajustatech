<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_receivables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('counterparty_name');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->uuid('company_cash_id');
            $table->string('status')->default('pending');
            $table->string('cash_flow_status')->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->foreign('company_cash_id')->references('id')->on('company_cashes')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_receivables');
    }
};