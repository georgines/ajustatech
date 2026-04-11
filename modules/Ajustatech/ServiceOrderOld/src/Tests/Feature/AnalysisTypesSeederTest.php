<?php

namespace Ajustatech\ServiceOrderOld\Tests\Feature;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Ajustatech\ServiceOrderOld\Database\Seeders\AnalysisTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisTypesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_complete_notebook_and_computer_analysis_models(): void
    {
        $this->seed(AnalysisTypesSeeder::class);

        $notebook = AnalysisType::query()->where('slug', 'analise-notebook-completa')->first();
        $computer = AnalysisType::query()->where('slug', 'analise-computador-completa')->first();

        $this->assertNotNull($notebook);
        $this->assertNotNull($computer);

        $this->assertGreaterThanOrEqual(2, $notebook->sections()->count());
        $this->assertGreaterThanOrEqual(2, $computer->sections()->count());

        $this->assertDatabaseHas('analysis_technical_actions', ['slug' => 'substituicao-carcaca']);
        $this->assertDatabaseHas('analysis_technical_action_prices', ['currency' => 'BRL']);
        $this->assertDatabaseHas('analysis_conditional_rules', ['target_type' => 'complementary_field', 'effect' => 'require']);
        $this->assertDatabaseHas('analysis_consequences', ['should_generate_budget' => true]);
        $this->assertDatabaseHas('analysis_questions', ['is_repeatable' => true]);
        $this->assertDatabaseHas('service_catalog_services', ['name' => 'Analise de Notebook', 'base_price' => 120.00, 'is_reusable' => true]);
        $this->assertDatabaseHas('service_catalog_services', ['name' => 'Analise de Computador', 'base_price' => 140.00, 'is_reusable' => true]);
    }
}
