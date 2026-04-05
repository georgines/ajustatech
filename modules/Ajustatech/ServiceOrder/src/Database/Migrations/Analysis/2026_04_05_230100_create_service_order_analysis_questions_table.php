<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_analysis_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_service_id');
            $table->uuid('parent_question_id')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->string('section_name', 120)->default('Geral');
            $table->text('question_text');
            $table->string('question_type', 20);
            $table->boolean('is_required')->default(false);
            $table->text('technical_description')->nullable();
            $table->boolean('is_technical_description_required')->default(false);
            $table->json('images_json')->nullable();
            $table->boolean('is_image_required')->default(false);
            $table->unsignedTinyInteger('required_images_count')->nullable();
            $table->string('condition_value', 255)->nullable();
            $table->json('options_json')->nullable();
            $table->json('answer_procedure_map_json')->nullable();
            $table->timestamps();

            $table->foreign('analysis_service_id', 'soaq_service_fk')
                ->references('id')
                ->on('service_order_analysis_services')
                ->onDelete('cascade');

            $table->foreign('parent_question_id', 'soaq_parent_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->nullOnDelete();

            $table->index(['analysis_service_id', 'sequence'], 'soaq_service_seq_idx');
            $table->index(['parent_question_id', 'sequence'], 'soaq_parent_seq_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_analysis_questions');
    }
};
