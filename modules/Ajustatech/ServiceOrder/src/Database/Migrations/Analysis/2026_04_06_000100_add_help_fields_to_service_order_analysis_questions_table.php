<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_analysis_questions', function (Blueprint $table) {
            $table->boolean('has_help')->default(false)->after('required_images_count');
            $table->text('help_content')->nullable()->after('has_help');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_analysis_questions', function (Blueprint $table) {
            $table->dropColumn(['has_help', 'help_content']);
        });
    }
};
