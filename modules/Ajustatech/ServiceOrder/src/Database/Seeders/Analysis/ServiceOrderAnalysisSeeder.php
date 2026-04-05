<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Seeder;

class ServiceOrderAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceOrderAnalysisService::query()->exists()) {
            return;
        }

        ServiceOrderAnalysisService::createWithQuestions([
            'name' => 'Analise de notebook',
            'description' => 'Diagnostico sequencial de hardware e software.',
            'value' => 90.00,
        ], [
            [
                'client_key' => 'q1',
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'O equipamento liga normalmente?',
                'question_type' => 'yes_no',
                'is_required' => true,
                'technical_description' => 'Verifique LED, tela, cooler e sinais sonoros.',
                'is_technical_description_required' => true,
                'images_json' => [
                    'https://example.com/analise/hardware-1.jpg',
                ],
                'is_image_required' => false,
                'required_images_count' => null,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'q2',
                'sequence' => 2,
                'section_name' => 'Hardware',
                'question_text' => 'O SSD esta funcionando?',
                'question_type' => 'yes_no',
                'is_required' => true,
                'technical_description' => 'Confirme leitura/escrita e status SMART.',
                'is_technical_description_required' => true,
                'images_json' => [],
                'is_image_required' => false,
                'required_images_count' => null,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'q3',
                'sequence' => 3,
                'section_name' => 'Sistema',
                'question_text' => 'Qual componente apresenta falha?',
                'question_type' => 'select',
                'is_required' => true,
                'technical_description' => 'Selecione o componente com defeito principal.',
                'is_technical_description_required' => false,
                'images_json' => [],
                'is_image_required' => false,
                'required_images_count' => null,
                'options_json' => [
                    ['key' => 'opt-ssd', 'label' => 'SSD', 'procedure_id' => ''],
                    ['key' => 'opt-ram', 'label' => 'Memoria RAM', 'procedure_id' => ''],
                    ['key' => 'opt-battery', 'label' => 'Bateria', 'procedure_id' => ''],
                ],
                'answer_procedure_map_json' => [
                    'opt-ssd' => ['procedure_id' => ''],
                    'opt-ram' => ['procedure_id' => ''],
                    'opt-battery' => ['procedure_id' => ''],
                ],
            ],
        ]);
    }
}
