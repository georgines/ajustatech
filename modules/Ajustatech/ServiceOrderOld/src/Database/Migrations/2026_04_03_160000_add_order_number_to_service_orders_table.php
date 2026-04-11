<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_number')->nullable()->after('id');
        });

        $orders = DB::table('service_orders')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id']);

        $counter = 1;
        foreach ($orders as $order) {
            DB::table('service_orders')
                ->where('id', $order->id)
                ->update(['order_number' => $counter]);
            $counter++;
        }

        Schema::table('service_orders', function (Blueprint $table) {
            $table->unique('order_number', 'service_orders_order_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropUnique('service_orders_order_number_unique');
            $table->dropColumn('order_number');
        });
    }
};

