<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_cash_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_cash_id');
            $table->foreign('company_cash_id')->references('id')->on('company_cashes')->onDelete('cascade');
            $table->decimal('total_inflows', 15, 2);
            $table->decimal('total_outflows', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_cash_balances');
    }
};

