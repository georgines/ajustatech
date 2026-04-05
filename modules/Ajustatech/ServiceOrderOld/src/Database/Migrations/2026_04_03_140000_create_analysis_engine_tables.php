<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('analysis_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_type_id');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('analysis_type_id')->references('id')->on('analysis_types')->onDelete('cascade');
            $table->index(['analysis_type_id', 'sort_order'], 'analysis_sections_type_order_idx');
        });

        Schema::create('analysis_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_section_id');
            $table->string('code')->nullable();
            $table->text('prompt');
            $table->string('answer_type', 50);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_repeatable')->default(false);
            $table->uuid('repeat_source_question_id')->nullable();
            $table->unsignedInteger('repeat_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('analysis_section_id')->references('id')->on('analysis_sections')->onDelete('cascade');
            $table->foreign('repeat_source_question_id')->references('id')->on('analysis_questions')->nullOnDelete();
            $table->index(['analysis_section_id', 'sort_order'], 'analysis_questions_section_order_idx');
        });

        Schema::create('analysis_question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_question_id');
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('analysis_question_id')->references('id')->on('analysis_questions')->onDelete('cascade');
            $table->index(['analysis_question_id', 'sort_order'], 'analysis_question_options_question_order_idx');
        });

        Schema::create('analysis_question_complementary_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_question_id');
            $table->string('name');
            $table->string('label');
            $table->string('field_type', 50);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->foreign('analysis_question_id', 'analysis_comp_fields_question_fk')
                ->references('id')
                ->on('analysis_questions')
                ->onDelete('cascade');
            $table->index(['analysis_question_id', 'sort_order'], 'analysis_comp_fields_question_order_idx');
        });

        Schema::create('analysis_technical_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('analysis_technical_action_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_technical_action_id');
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3)->default('BRL');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->foreign('analysis_technical_action_id', 'analysis_action_prices_action_fk')
                ->references('id')
                ->on('analysis_technical_actions')
                ->onDelete('cascade');
        });

        Schema::create('analysis_conditional_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_question_id');
            $table->string('target_type', 60);
            $table->uuid('target_id');
            $table->string('operator', 40)->default('equals');
            $table->string('expected_value')->nullable();
            $table->uuid('expected_option_id')->nullable();
            $table->string('effect', 40);
            $table->string('effect_value')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('analysis_question_id')->references('id')->on('analysis_questions')->onDelete('cascade');
            $table->foreign('expected_option_id')->references('id')->on('analysis_question_options')->nullOnDelete();
            $table->index(['analysis_question_id', 'sort_order'], 'analysis_rules_question_order_idx');
            $table->index(['target_type', 'target_id'], 'analysis_rules_target_idx');
        });

        Schema::create('analysis_consequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('analysis_question_id');
            $table->uuid('analysis_question_option_id')->nullable();
            $table->string('match_operator', 40)->default('equals');
            $table->string('match_value')->nullable();
            $table->string('severity', 30)->default('medium');
            $table->text('description');
            $table->uuid('analysis_technical_action_id')->nullable();
            $table->boolean('should_generate_budget')->default(true);
            $table->boolean('visible_to_technician')->default(true);
            $table->string('recommendation_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('analysis_question_id')->references('id')->on('analysis_questions')->onDelete('cascade');
            $table->foreign('analysis_question_option_id', 'analysis_consequence_option_fk')
                ->references('id')
                ->on('analysis_question_options')
                ->nullOnDelete();
            $table->foreign('analysis_technical_action_id', 'analysis_consequence_action_fk')
                ->references('id')
                ->on('analysis_technical_actions')
                ->nullOnDelete();
        });

        Schema::create('service_order_analysis_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('analysis_type_id')->nullable();
            $table->json('analysis_type_snapshot')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('initial_notes')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('analysis_type_id')->references('id')->on('analysis_types')->nullOnDelete();
            $table->index(['service_order_id', 'status'], 'so_analysis_service_order_status_idx');
        });

        Schema::create('service_order_analysis_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_service_id');
            $table->uuid('source_section_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('service_order_analysis_service_id', 'so_analysis_sections_service_fk')
                ->references('id')
                ->on('service_order_analysis_services')
                ->onDelete('cascade');
            $table->index(['service_order_analysis_service_id', 'sort_order'], 'so_analysis_sections_service_order_idx');
        });

        Schema::create('service_order_analysis_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_service_id');
            $table->uuid('service_order_analysis_section_id');
            $table->uuid('source_question_id')->nullable();
            $table->string('question_code')->nullable();
            $table->text('prompt');
            $table->string('answer_type', 50);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_repeatable')->default(false);
            $table->string('repetition_group')->nullable();
            $table->unsignedInteger('repetition_index')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('service_order_analysis_service_id', 'so_analysis_questions_service_fk')
                ->references('id')
                ->on('service_order_analysis_services')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_section_id', 'so_analysis_questions_section_fk')
                ->references('id')
                ->on('service_order_analysis_sections')
                ->onDelete('cascade');
            $table->index(['service_order_analysis_service_id', 'service_order_analysis_section_id'], 'so_analysis_questions_service_section_idx');
        });

        Schema::create('service_order_analysis_question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_question_id');
            $table->uuid('source_option_id')->nullable();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('service_order_analysis_question_id', 'so_analysis_q_options_question_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->onDelete('cascade');
            $table->index(['service_order_analysis_question_id', 'sort_order'], 'so_analysis_q_options_question_order_idx');
        });

        Schema::create('service_order_analysis_complementary_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_question_id');
            $table->uuid('source_complementary_field_id')->nullable();
            $table->string('name');
            $table->string('label');
            $table->string('field_type', 50);
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->foreign('service_order_analysis_question_id', 'so_analysis_comp_fields_question_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->onDelete('cascade');
            $table->index(['service_order_analysis_question_id', 'sort_order'], 'so_analysis_comp_fields_question_order_idx');
        });

        Schema::create('service_order_analysis_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_service_id');
            $table->uuid('service_order_analysis_question_id');
            $table->foreignId('answered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->decimal('answer_number', 15, 4)->nullable();
            $table->date('answer_date')->nullable();
            $table->boolean('answer_boolean')->nullable();
            $table->json('answer_json')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('service_order_analysis_service_id', 'so_analysis_responses_service_fk')
                ->references('id')
                ->on('service_order_analysis_services')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_question_id', 'so_analysis_responses_question_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->onDelete('cascade');
            $table->unique('service_order_analysis_question_id', 'so_analysis_response_unique_question');
        });

        Schema::create('service_order_analysis_complementary_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_response_id');
            $table->uuid('service_order_analysis_complementary_field_id');
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->foreign('service_order_analysis_response_id', 'so_analysis_comp_responses_response_fk')
                ->references('id')
                ->on('service_order_analysis_responses')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_complementary_field_id', 'so_analysis_comp_responses_field_fk')
                ->references('id')
                ->on('service_order_analysis_complementary_fields')
                ->onDelete('cascade');
            $table->unique(
                ['service_order_analysis_response_id', 'service_order_analysis_complementary_field_id'],
                'so_analysis_comp_response_unique'
            );
        });

        Schema::create('service_order_analysis_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_question_id');
            $table->uuid('service_order_analysis_response_id')->nullable();
            $table->uuid('service_order_analysis_complementary_response_id')->nullable();
            $table->uuid('service_order_analysis_complementary_field_id')->nullable();
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 255)->nullable();
            $table->string('extension', 20);
            $table->unsignedBigInteger('size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('service_order_analysis_question_id', 'so_analysis_attachments_question_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_response_id', 'so_analysis_attachments_response_fk')
                ->references('id')
                ->on('service_order_analysis_responses')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_complementary_response_id', 'so_analysis_attachments_comp_response_fk')
                ->references('id')
                ->on('service_order_analysis_complementary_responses')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_complementary_field_id', 'so_analysis_attachments_comp_field_fk')
                ->references('id')
                ->on('service_order_analysis_complementary_fields')
                ->nullOnDelete();
        });

        Schema::create('service_order_technical_findings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_analysis_service_id');
            $table->uuid('service_order_analysis_question_id')->nullable();
            $table->uuid('service_order_analysis_response_id')->nullable();
            $table->text('description');
            $table->string('severity', 30)->default('medium');
            $table->uuid('analysis_technical_action_id')->nullable();
            $table->boolean('should_generate_budget')->default(true);
            $table->boolean('generated_automatically')->default(true);
            $table->json('consequence_snapshot')->nullable();
            $table->string('status', 30)->default('open');
            $table->timestamps();

            $table->foreign('service_order_analysis_service_id', 'so_findings_service_fk')
                ->references('id')
                ->on('service_order_analysis_services')
                ->onDelete('cascade');
            $table->foreign('service_order_analysis_question_id', 'so_findings_question_fk')
                ->references('id')
                ->on('service_order_analysis_questions')
                ->nullOnDelete();
            $table->foreign('service_order_analysis_response_id', 'so_findings_response_fk')
                ->references('id')
                ->on('service_order_analysis_responses')
                ->nullOnDelete();
            $table->foreign('analysis_technical_action_id', 'so_findings_action_fk')
                ->references('id')
                ->on('analysis_technical_actions')
                ->nullOnDelete();
            $table->index(['service_order_analysis_service_id', 'status'], 'so_findings_service_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_technical_findings');
        Schema::dropIfExists('service_order_analysis_attachments');
        Schema::dropIfExists('service_order_analysis_complementary_responses');
        Schema::dropIfExists('service_order_analysis_responses');
        Schema::dropIfExists('service_order_analysis_complementary_fields');
        Schema::dropIfExists('service_order_analysis_question_options');
        Schema::dropIfExists('service_order_analysis_questions');
        Schema::dropIfExists('service_order_analysis_sections');
        Schema::dropIfExists('service_order_analysis_services');
        Schema::dropIfExists('analysis_consequences');
        Schema::dropIfExists('analysis_conditional_rules');
        Schema::dropIfExists('analysis_technical_action_prices');
        Schema::dropIfExists('analysis_technical_actions');
        Schema::dropIfExists('analysis_question_complementary_fields');
        Schema::dropIfExists('analysis_question_options');
        Schema::dropIfExists('analysis_questions');
        Schema::dropIfExists('analysis_sections');
        Schema::dropIfExists('analysis_types');
    }
};
