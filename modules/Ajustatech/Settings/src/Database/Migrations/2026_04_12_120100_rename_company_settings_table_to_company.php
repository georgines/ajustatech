<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('company_settings', 'company');
    }

    public function down(): void
    {
        Schema::rename('company', 'company_settings');
    }
};
