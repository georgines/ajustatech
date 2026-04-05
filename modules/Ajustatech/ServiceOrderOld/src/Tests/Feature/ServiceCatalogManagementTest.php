<?php

namespace Ajustatech\ServiceOrderOld\Tests\Feature;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Ajustatech\ServiceOrderOld\Livewire\ServiceCatalogManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_analysis_type_with_question_options_complementary_rules_and_consequences(): void
    {
        $component = Livewire::test(ServiceCatalogManagement::class)
            ->set('name', 'Analise de Notebook')
            ->set('description', 'Modelo tecnico')
            ->set('sections.0.name', 'Inspecao fisica')
            ->set('sections.0.questions.0.prompt', 'Carcaca quebrada?')
            ->set('sections.0.questions.0.answer_type', 'single_select')
            ->set('sections.0.questions.0.options.0.label', 'Nao')
            ->set('sections.0.questions.0.options.0.value', 'nao')
            ->set('sections.0.questions.0.options.1.label', 'Sim')
            ->set('sections.0.questions.0.options.1.value', 'sim')
            ->call('addComplementaryField', 0, 0)
            ->set('sections.0.questions.0.complementary_fields.0.name', 'foto_evidencia')
            ->set('sections.0.questions.0.complementary_fields.0.label', 'Foto de evidencia')
            ->set('sections.0.questions.0.complementary_fields.0.field_type', 'photo');

        $yesOptionTempId = $component->get('sections.0.questions.0.options.1.temp_id');
        $photoFieldTempId = $component->get('sections.0.questions.0.complementary_fields.0.temp_id');

        $component
            ->call('addConditionalRule', 0, 0)
            ->set('sections.0.questions.0.conditional_rules.0.expected_option_temp_id', $yesOptionTempId)
            ->set('sections.0.questions.0.conditional_rules.0.target_field_temp_id', $photoFieldTempId)
            ->set('sections.0.questions.0.conditional_rules.0.effect', 'require')
            ->call('addConsequence', 0, 0)
            ->set('sections.0.questions.0.consequences.0.expected_option_temp_id', $yesOptionTempId)
            ->set('sections.0.questions.0.consequences.0.severity', 'critical')
            ->set('sections.0.questions.0.consequences.0.description', 'Dano estrutural identificado')
            ->set('sections.0.questions.0.consequences.0.technical_action_name', 'Substituicao de carcaca')
            ->set('sections.0.questions.0.consequences.0.should_generate_budget', true)
            ->call('save')
            ->assertHasNoErrors();

        $type = AnalysisType::query()->where('name', 'Analise de Notebook')->first();
        $this->assertNotNull($type);

        $this->assertDatabaseHas('analysis_sections', [
            'analysis_type_id' => $type->id,
            'name' => 'Inspecao fisica',
        ]);

        $question = $type->sections()->first()->questions()->first();
        $this->assertNotNull($question);

        $this->assertDatabaseHas('analysis_question_options', [
            'analysis_question_id' => $question->id,
            'value' => 'sim',
        ]);

        $this->assertDatabaseHas('analysis_question_complementary_fields', [
            'analysis_question_id' => $question->id,
            'name' => 'foto_evidencia',
            'field_type' => 'photo',
        ]);

        $this->assertDatabaseHas('analysis_conditional_rules', [
            'analysis_question_id' => $question->id,
            'effect' => 'require',
            'target_type' => 'complementary_field',
        ]);

        $this->assertDatabaseHas('analysis_consequences', [
            'analysis_question_id' => $question->id,
            'severity' => 'critical',
            'description' => 'Dano estrutural identificado',
            'should_generate_budget' => true,
        ]);
    }

    public function test_blocks_saving_select_question_without_two_valid_options(): void
    {
        Livewire::test(ServiceCatalogManagement::class)
            ->set('name', 'Analise de Desktop')
            ->set('sections.0.name', 'Teste inicial')
            ->set('sections.0.questions.0.prompt', 'Liga corretamente?')
            ->set('sections.0.questions.0.answer_type', 'single_select')
            ->set('sections.0.questions.0.options.0.label', 'Sim')
            ->set('sections.0.questions.0.options.0.value', 'sim')
            ->set('sections.0.questions.0.options.1.label', '')
            ->set('sections.0.questions.0.options.1.value', '')
            ->call('save')
            ->assertHasErrors(['sections.0.questions.0.options']);
    }
}
