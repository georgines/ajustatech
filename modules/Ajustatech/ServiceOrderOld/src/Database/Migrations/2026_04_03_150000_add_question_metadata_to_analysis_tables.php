<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_questions', function (Blueprint $table) {
            $table->text('help_text')->nullable()->after('prompt');
            $table->string('technician_note_label')->nullable()->after('help_text');
            $table->boolean('requires_photo_evidence')->default(false)->after('is_repeatable');
        });

        Schema::table('service_order_analysis_questions', function (Blueprint $table) {
            $table->text('help_text')->nullable()->after('prompt');
            $table->string('technician_note_label')->nullable()->after('help_text');
            $table->boolean('requires_photo_evidence')->default(false)->after('is_repeatable');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_analysis_questions', function (Blueprint $table) {
            $table->dropColumn(['help_text', 'technician_note_label', 'requires_photo_evidence']);
        });

        Schema::table('analysis_questions', function (Blueprint $table) {
            $table->dropColumn(['help_text', 'technician_note_label', 'requires_photo_evidence']);
        });
    }
};

