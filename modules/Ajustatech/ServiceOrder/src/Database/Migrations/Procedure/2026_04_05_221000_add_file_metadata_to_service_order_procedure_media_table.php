<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_procedure_media', function (Blueprint $table) {
            $table->string('mime_type', 120)->nullable()->after('original_name');
            $table->string('extension', 20)->nullable()->after('mime_type');
            $table->unsignedBigInteger('size')->nullable()->after('extension');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_procedure_media', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'extension', 'size']);
        });
    }
};

