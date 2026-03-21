<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_cashes', function (Blueprint $table) {
            if (!Schema::hasColumn('company_cashes', 'is_managerial')) {
                $table->boolean('is_managerial')->default(true)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_cashes', function (Blueprint $table) {
            if (Schema::hasColumn('company_cashes', 'is_managerial')) {
                $table->dropColumn('is_managerial');
            }
        });
    }
};

