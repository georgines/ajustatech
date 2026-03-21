<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_receivables', function (Blueprint $table) {
            if (!Schema::hasColumn('financial_receivables', 'payment_method_type')) {
                $table->string('payment_method_type')->nullable()->after('company_cash_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financial_receivables', function (Blueprint $table) {
            if (Schema::hasColumn('financial_receivables', 'payment_method_type')) {
                $table->dropColumn('payment_method_type');
            }
        });
    }
};