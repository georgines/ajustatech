<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_cash_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_cash_sessions', 'opening_payment_method_type')) {
                $table->string('opening_payment_method_type')->nullable()->after('source_company_cash_id');
            }

            if (!Schema::hasColumn('sales_cash_sessions', 'closing_payment_method_type')) {
                $table->string('closing_payment_method_type')->nullable()->after('opening_payment_method_type');
            }

            if (!Schema::hasColumn('sales_cash_sessions', 'destination_company_cash_id')) {
                $table->uuid('destination_company_cash_id')->nullable()->after('source_company_cash_id');
                $table->foreign('destination_company_cash_id')->references('id')->on('company_cashes')->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_cash_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sales_cash_sessions', 'destination_company_cash_id')) {
                $table->dropForeign(['destination_company_cash_id']);
                $table->dropColumn('destination_company_cash_id');
            }
            if (Schema::hasColumn('sales_cash_sessions', 'opening_payment_method_type')) {
                $table->dropColumn('opening_payment_method_type');
            }
            if (Schema::hasColumn('sales_cash_sessions', 'closing_payment_method_type')) {
                $table->dropColumn('closing_payment_method_type');
            }
        });
    }
};