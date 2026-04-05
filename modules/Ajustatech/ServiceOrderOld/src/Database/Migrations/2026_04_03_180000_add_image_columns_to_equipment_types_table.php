<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->string('image_disk', 64)->nullable()->after('is_active');
            $table->string('image_path', 2048)->nullable()->after('image_disk');
            $table->string('image_original_name')->nullable()->after('image_path');
            $table->string('image_mime_type', 100)->nullable()->after('image_original_name');
            $table->unsignedBigInteger('image_size')->nullable()->after('image_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->dropColumn([
                'image_disk',
                'image_path',
                'image_original_name',
                'image_mime_type',
                'image_size',
            ]);
        });
    }
};
