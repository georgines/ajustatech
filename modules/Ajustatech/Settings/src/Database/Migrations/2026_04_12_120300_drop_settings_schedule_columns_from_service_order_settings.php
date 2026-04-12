<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_settings', function (Blueprint $table) {
            $table->dropColumn(['working_days_json', 'holidays_json']);
        });
    }

    public function down(): void
    {
        Schema::table('service_order_settings', function (Blueprint $table) {
            $table->json('working_days_json')->after('initial_order_number');
            $table->json('holidays_json')->nullable()->after('working_days_json');
        });
    }
};
