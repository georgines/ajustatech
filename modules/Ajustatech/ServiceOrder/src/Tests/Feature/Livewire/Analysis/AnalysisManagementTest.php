<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Analysis;

use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AnalysisManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_analysis_service_with_questions_and_procedure_rules(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise de notebook')
            ->set('description', 'Checklist tecnico completo')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.sequence', 1)
            ->set('questions.0.question_text', 'O SSD esta funcionando?')
            ->set('questions.0.question_type', 'yes_no')
            ->set('questions.0.answer_procedure_map.no.procedure_id', '')
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $this->assertDatabaseHas('service_order_analysis_services', [
            'name' => 'Analise de notebook',
            'value' => '90.00',
        ]);

        $this->assertDatabaseHas('service_order_analysis_questions', [
            'section_name' => 'Hardware',
            'question_text' => 'O SSD esta funcionando?',
            'question_type' => 'yes_no',
        ]);

        $question = DB::table('service_order_analysis_questions')
            ->where('question_text', 'O SSD esta funcionando?')
            ->first();

        $this->assertNotNull($question);
        $this->assertStringContainsString('yes', (string) $question->answer_procedure_map_json);
        $this->assertNull($question->images_json);
    }
}
