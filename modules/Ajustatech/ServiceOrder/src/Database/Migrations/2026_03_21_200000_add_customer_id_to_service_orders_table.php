<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('equipment_type_id');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index('customer_id', 'service_orders_customer_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex('service_orders_customer_id_index');
            $table->dropColumn('customer_id');
        });
    }
};

