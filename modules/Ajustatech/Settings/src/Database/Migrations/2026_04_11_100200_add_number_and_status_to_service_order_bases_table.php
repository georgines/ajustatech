<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_bases', function (Blueprint $table) {
            $table->unsignedBigInteger('order_number')->nullable()->after('id');
            $table->uuid('status_flow_id')->nullable()->after('order_number');

            $table->unique('order_number', 'sob_order_number_uidx');
            $table->index('status_flow_id', 'sob_status_flow_idx');
            $table->foreign('status_flow_id', 'sob_status_flow_fk')
                ->references('id')
                ->on('service_order_status_flows')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_order_bases', function (Blueprint $table) {
            $table->dropForeign('sob_status_flow_fk');
            $table->dropUnique('sob_order_number_uidx');
            $table->dropIndex('sob_status_flow_idx');

            $table->dropColumn(['order_number', 'status_flow_id']);
        });
    }
};


