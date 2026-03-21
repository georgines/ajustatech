<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_payment_method_costs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('financial_payment_method_id');
            $table->decimal('fixed_cost', 15, 2)->default(0);
            $table->decimal('percent_cost', 8, 4)->default(0);
            $table->string('brand')->nullable();
            $table->unsignedSmallInteger('installments')->nullable();
            $table->string('receipt_channel')->nullable();
            $table->timestamps();

            $table->foreign('financial_payment_method_id')->references('id')->on('financial_payment_methods')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_payment_method_costs');
    }
};