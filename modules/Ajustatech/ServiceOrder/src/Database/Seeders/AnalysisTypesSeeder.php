<?php

namespace Ajustatech\ServiceOrder\Database\Seeders;

use Ajustatech\ServiceOrder\Database\Models\AnalysisTechnicalAction;
use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalysisTypesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $actions = $this->seedTechnicalActions();
            $this->seedNotebookAnalysis($actions);
            $this->seedComputerAnalysis($actions);
            $this->syncServiceCatalogWithAnalysisTypes();
        });
    }

    private function seedTechnicalActions(): array
    {
        $payloads = [
            ['slug' => 'restauracao-carcaca', 'name' => 'Restauracao de carcaca', 'amount' => 220.00],
            ['slug' => 'substituicao-carcaca', 'name' => 'Substituicao de carcaca', 'amount' => 580.00],
            ['slug' => 'troca-porta-usb', 'name' => 'Troca de porta USB', 'amount' => 180.00],
            ['slug' => 'reparo-solda-fria', 'name' => 'Reparo de solda fria', 'amount' => 290.00],
            ['slug' => 'troca-memoria', 'name' => 'Substituicao de memoria RAM', 'amount' => 260.00],
            ['slug' => 'diagnostico-sem-acao', 'name' => 'Sem acao corretiva', 'amount' => 0.00],
        ];

        $actions = [];
        foreach ($payloads as $payload) {
            $action = AnalysisTechnicalAction::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    'name' => $payload['name'],
                    'description' => 'Acao tecnica padrao de analise.',
                    'is_active' => true,
                ]
            );

            $action->prices()->updateOrCreate(
                ['currency' => 'BRL', 'valid_from' => now()->toDateString()],
                ['amount' => $payload['amount'], 'valid_until' => null]
            );

            $actions[$payload['slug']] = $action;
        }

        return $actions;
    }

    private function seedNotebookAnalysis(array $actions): void
    {
        $type = AnalysisType::query()->updateOrCreate(
            ['slug' => 'analise-notebook-completa'],
            [
                'name' => 'Analise de Notebook',
                'description' => 'Fluxo completo de diagnostico tecnico para notebook.',
                'is_active' => true,
            ]
        );

        $type->sections()->delete();

        $sectionStructural = $type->sections()->create([
            'name' => 'Inspecao estrutural',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $qCase = $sectionStructural->questions()->create([
            'code' => 'NB-EST-001',
            'prompt' => 'Carcaca quebrada?',
            'help_text' => 'Inspecione tampa, base e area de dobradicas.',
            'technician_note_label' => 'Observacao tecnica da carcaca',
            'answer_type' => 'single_select',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => false,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $optNo = $qCase->options()->create(['label' => 'Nao', 'value' => 'nao', 'sort_order' => 1, 'is_active' => true]);
        $optYes = $qCase->options()->create(['label' => 'Sim', 'value' => 'sim', 'sort_order' => 2, 'is_active' => true]);
        $optSmall = $qCase->options()->create(['label' => 'Sim - Pequeno', 'value' => 'sim_pequeno', 'sort_order' => 3, 'is_active' => true]);
        $optMedium = $qCase->options()->create(['label' => 'Sim - Medio', 'value' => 'sim_medio', 'sort_order' => 4, 'is_active' => true]);
        $optCritical = $qCase->options()->create(['label' => 'Sim - Critico', 'value' => 'sim_critico', 'sort_order' => 5, 'is_active' => true]);
        $optVeryCritical = $qCase->options()->create(['label' => 'Sim - Muito Critico', 'value' => 'sim_muito_critico', 'sort_order' => 6, 'is_active' => true]);
        $optIrreparable = $qCase->options()->create(['label' => 'Sim - Irreparavel', 'value' => 'sim_irreparavel', 'sort_order' => 7, 'is_active' => true]);

        $fieldDamageNote = $qCase->complementaryFields()->create([
            'name' => 'observacao_dano_estrutural',
            'label' => 'Observacao tecnica do dano',
            'field_type' => 'text',
            'sort_order' => 1,
            'is_required' => false,
            'is_active' => true,
            'configuration' => ['max_length' => 2000],
        ]);

        $fieldDamagePhoto = $qCase->complementaryFields()->create([
            'name' => 'foto_dano_estrutural',
            'label' => 'Foto de evidencia',
            'field_type' => 'photo',
            'sort_order' => 2,
            'is_required' => false,
            'is_active' => true,
            'configuration' => ['allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp']],
        ]);

        $rules = [
            [$optYes, 'require'], [$optSmall, 'require'], [$optMedium, 'require'], [$optCritical, 'require'], [$optVeryCritical, 'require'], [$optIrreparable, 'require'],
        ];

        foreach ($rules as [$option, $effect]) {
            DB::table('analysis_conditional_rules')->insert([
                'id' => (string) Str::uuid(),
                'analysis_question_id' => $qCase->id,
                'target_type' => 'complementary_field',
                'target_id' => $fieldDamageNote->id,
                'operator' => 'equals',
                'expected_option_id' => $option->id,
                'expected_value' => null,
                'effect' => $effect,
                'effect_value' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('analysis_conditional_rules')->insert([
                'id' => (string) Str::uuid(),
                'analysis_question_id' => $qCase->id,
                'target_type' => 'complementary_field',
                'target_id' => $fieldDamagePhoto->id,
                'operator' => 'equals',
                'expected_option_id' => $option->id,
                'expected_value' => null,
                'effect' => 'require',
                'effect_value' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $qCase->consequences()->createMany([
            [
                'analysis_question_option_id' => $optNo->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'low',
                'description' => 'Sem dano estrutural.',
                'analysis_technical_action_id' => $actions['diagnostico-sem-acao']->id,
                'should_generate_budget' => false,
                'visible_to_technician' => true,
                'recommendation_text' => 'Sem acao estrutural.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $optSmall->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'medium',
                'description' => 'Dano estrutural pequeno.',
                'analysis_technical_action_id' => $actions['restauracao-carcaca']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Recomendar restauracao de carcaca.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $optMedium->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'high',
                'description' => 'Dano estrutural medio.',
                'analysis_technical_action_id' => $actions['restauracao-carcaca']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Avaliar restauracao reforcada.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $optCritical->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'critical',
                'description' => 'Dano estrutural critico.',
                'analysis_technical_action_id' => $actions['substituicao-carcaca']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Recomendar substituicao de carcaca.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $optVeryCritical->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'very_critical',
                'description' => 'Dano estrutural muito critico.',
                'analysis_technical_action_id' => $actions['substituicao-carcaca']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Substituicao imediata de carcaca.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $optIrreparable->id,
                'match_operator' => 'equals',
                'match_value' => null,
                'severity' => 'irreparable',
                'description' => 'Estrutura irreparavel.',
                'analysis_technical_action_id' => $actions['substituicao-carcaca']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Substituicao total da estrutura.',
                'is_active' => true,
            ],
        ]);

        $sectionInterfaces = $type->sections()->create([
            'name' => 'Interfaces e conectividade',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $qUsbCount = $sectionInterfaces->questions()->create([
            'code' => 'NB-INT-001',
            'prompt' => 'Quantidade de portas USB para teste',
            'help_text' => 'Informe o total de portas USB fisicas.',
            'technician_note_label' => 'Quantidade de portas',
            'answer_type' => 'number',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => true,
            'requires_photo_evidence' => false,
            'repeat_limit' => 10,
            'is_active' => true,
        ]);

        $qUsbStatus = $sectionInterfaces->questions()->create([
            'code' => 'NB-INT-002',
            'prompt' => 'Porta USB apresenta falha de reconhecimento?',
            'help_text' => 'Execute teste funcional com dispositivo de armazenamento.',
            'technician_note_label' => 'Detalhe da porta USB',
            'answer_type' => 'yes_no',
            'sort_order' => 2,
            'is_required' => true,
            'is_repeatable' => true,
            'repeat_source_question_id' => $qUsbCount->id,
            'repeat_limit' => 10,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $usbNo = $qUsbStatus->options()->create(['label' => 'Nao', 'value' => 'nao', 'sort_order' => 1, 'is_active' => true]);
        $usbYes = $qUsbStatus->options()->create(['label' => 'Sim', 'value' => 'sim', 'sort_order' => 2, 'is_active' => true]);
        $usbDetail = $qUsbStatus->complementaryFields()->create([
            'name' => 'detalhe_falha_usb',
            'label' => 'Detalhe tecnico da falha',
            'field_type' => 'text',
            'sort_order' => 1,
            'is_required' => false,
            'is_active' => true,
            'configuration' => ['max_length' => 1000],
        ]);

        DB::table('analysis_conditional_rules')->insert([
            'id' => (string) Str::uuid(),
            'analysis_question_id' => $qUsbStatus->id,
            'target_type' => 'complementary_field',
            'target_id' => $usbDetail->id,
            'operator' => 'equals',
            'expected_option_id' => $usbYes->id,
            'expected_value' => null,
            'effect' => 'require',
            'effect_value' => null,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $qUsbStatus->consequences()->createMany([
            [
                'analysis_question_option_id' => $usbNo->id,
                'severity' => 'low',
                'description' => 'Porta USB sem falha.',
                'analysis_technical_action_id' => $actions['diagnostico-sem-acao']->id,
                'should_generate_budget' => false,
                'visible_to_technician' => true,
                'recommendation_text' => 'Sem intervencao na USB.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $usbYes->id,
                'severity' => 'high',
                'description' => 'Falha de reconhecimento em porta USB.',
                'analysis_technical_action_id' => $actions['troca-porta-usb']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Avaliar troca de porta USB.',
                'is_active' => true,
            ],
        ]);
    }

    private function seedComputerAnalysis(array $actions): void
    {
        $type = AnalysisType::query()->updateOrCreate(
            ['slug' => 'analise-computador-completa'],
            [
                'name' => 'Analise de Computador',
                'description' => 'Fluxo completo de diagnostico para desktop/computador.',
                'is_active' => true,
            ]
        );

        $type->sections()->delete();

        $sectionBoot = $type->sections()->create([
            'name' => 'Inicializacao e video',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $qBoot = $sectionBoot->questions()->create([
            'code' => 'PC-BOT-001',
            'prompt' => 'Computador inicializa corretamente?',
            'help_text' => 'Verifique POST, beep e exibicao de video.',
            'technician_note_label' => 'Observacao de inicializacao',
            'answer_type' => 'single_select',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => false,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $bootYes = $qBoot->options()->create(['label' => 'Sim', 'value' => 'sim', 'sort_order' => 1, 'is_active' => true]);
        $bootNo = $qBoot->options()->create(['label' => 'Nao', 'value' => 'nao', 'sort_order' => 2, 'is_active' => true]);
        $bootNoVideo = $qBoot->options()->create(['label' => 'Sem video', 'value' => 'sem_video', 'sort_order' => 3, 'is_active' => true]);

        $bootPhoto = $qBoot->complementaryFields()->create([
            'name' => 'foto_tela_ou_debug_led',
            'label' => 'Foto da tela / led de debug',
            'field_type' => 'photo',
            'sort_order' => 1,
            'is_required' => false,
            'is_active' => true,
            'configuration' => ['allowed_extensions' => ['jpg', 'jpeg', 'png']],
        ]);

        DB::table('analysis_conditional_rules')->insert([
            'id' => (string) Str::uuid(),
            'analysis_question_id' => $qBoot->id,
            'target_type' => 'complementary_field',
            'target_id' => $bootPhoto->id,
            'operator' => 'equals',
            'expected_option_id' => $bootNoVideo->id,
            'expected_value' => null,
            'effect' => 'require',
            'effect_value' => null,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $qBoot->consequences()->createMany([
            [
                'analysis_question_option_id' => $bootYes->id,
                'severity' => 'low',
                'description' => 'Inicializacao normal.',
                'analysis_technical_action_id' => $actions['diagnostico-sem-acao']->id,
                'should_generate_budget' => false,
                'visible_to_technician' => true,
                'recommendation_text' => 'Prosseguir com testes adicionais.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $bootNo->id,
                'severity' => 'high',
                'description' => 'Falha de inicializacao.',
                'analysis_technical_action_id' => $actions['reparo-solda-fria']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Investigar circuito primario.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $bootNoVideo->id,
                'severity' => 'critical',
                'description' => 'Ausencia de video interno.',
                'analysis_technical_action_id' => $actions['reparo-solda-fria']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Diagnosticar GPU/memoria/BIOS.',
                'is_active' => true,
            ],
        ]);

        $sectionMemory = $type->sections()->create([
            'name' => 'Memoria e armazenamento',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $qSlots = $sectionMemory->questions()->create([
            'code' => 'PC-MEM-001',
            'prompt' => 'Quantidade de slots de memoria para teste',
            'help_text' => 'Informe a quantidade total de slots fisicos.',
            'technician_note_label' => 'Quantidade de slots',
            'answer_type' => 'number',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => true,
            'repeat_limit' => 8,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $qSlotFail = $sectionMemory->questions()->create([
            'code' => 'PC-MEM-002',
            'prompt' => 'Slot de memoria apresenta falha?',
            'help_text' => 'Teste cada slot com modulo validado.',
            'technician_note_label' => 'Detalhe da falha no slot',
            'answer_type' => 'yes_no',
            'sort_order' => 2,
            'is_required' => true,
            'is_repeatable' => true,
            'repeat_source_question_id' => $qSlots->id,
            'repeat_limit' => 8,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $slotNo = $qSlotFail->options()->create(['label' => 'Nao', 'value' => 'nao', 'sort_order' => 1, 'is_active' => true]);
        $slotYes = $qSlotFail->options()->create(['label' => 'Sim', 'value' => 'sim', 'sort_order' => 2, 'is_active' => true]);

        $qSlotFail->consequences()->createMany([
            [
                'analysis_question_option_id' => $slotNo->id,
                'severity' => 'low',
                'description' => 'Slot funcional.',
                'analysis_technical_action_id' => $actions['diagnostico-sem-acao']->id,
                'should_generate_budget' => false,
                'visible_to_technician' => true,
                'recommendation_text' => 'Sem acao para o slot.',
                'is_active' => true,
            ],
            [
                'analysis_question_option_id' => $slotYes->id,
                'severity' => 'high',
                'description' => 'Falha em slot de memoria.',
                'analysis_technical_action_id' => $actions['troca-memoria']->id,
                'should_generate_budget' => true,
                'visible_to_technician' => true,
                'recommendation_text' => 'Recomendar reparo/substituicao do slot.',
                'is_active' => true,
            ],
        ]);
    }

    private function syncServiceCatalogWithAnalysisTypes(): void
    {
        $types = AnalysisType::query()
            ->whereIn('slug', ['analise-notebook-completa', 'analise-computador-completa'])
            ->get()
            ->keyBy('slug');

        $catalogMap = [
            'analise-notebook-completa' => 120.00,
            'analise-computador-completa' => 140.00,
        ];

        foreach ($catalogMap as $slug => $basePrice) {
            $type = $types->get($slug);
            if (!$type) {
                continue;
            }

            ServiceCatalogService::query()->updateOrCreate(
                ['name' => $type->name],
                [
                    'description' => $type->description,
                    'base_price' => $basePrice,
                    'is_active' => (bool) $type->is_active,
                    'is_reusable' => true,
                ]
            );
        }
    }
}
