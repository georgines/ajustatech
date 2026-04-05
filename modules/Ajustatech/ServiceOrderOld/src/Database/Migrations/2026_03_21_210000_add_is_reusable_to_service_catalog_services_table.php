<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_catalog_services', function (Blueprint $table) {
            $table->boolean('is_reusable')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('service_catalog_services', function (Blueprint $table) {
            $table->dropColumn('is_reusable');
        });
    }
};

