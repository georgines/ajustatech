<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisServiceFactory;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceOrderAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceOrderAnalysisService::query()->exists()) {
            return;
        }

        $services = ServiceOrderAnalysisServiceFactory::new()
            ->count(2)
            ->make()
            ->values();

        $items = [
            [
                'service' => array_merge($services[0]->toArray(), [
                    'name' => 'Analise de notebook',
                    'description' => 'Diagnostico sequencial de hardware e software.',
                    'value' => 90.00,
                ]),
                'questions' => [
                    [
                        'client_key' => (string) Str::uuid(),
                        'sequence' => 1,
                        'section_name' => 'Hardware',
                        'question_text' => 'O equipamento liga normalmente?',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'technical_description' => 'Verifique LED, tela, cooler e sinais sonoros.',
                        'is_technical_description_required' => true,
                        'images_json' => ['https://example.com/analise/hardware-1.jpg'],
                        'is_image_required' => false,
                        'required_images_count' => null,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                    [
                        'client_key' => 'q-seed-ssd',
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
                        'client_key' => (string) Str::uuid(),
                        'parent_client_key' => 'q-seed-ssd',
                        'condition_value' => 'no',
                        'sequence' => 3,
                        'section_name' => 'Hardware',
                        'question_text' => 'Subpergunta: o disco nao aparece na BIOS?',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'technical_description' => 'Validar deteccao basica em BIOS/UEFI.',
                        'is_technical_description_required' => false,
                        'images_json' => [],
                        'is_image_required' => false,
                        'required_images_count' => null,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => array_merge($services[1]->toArray(), [
                    'name' => 'Analise de desktop',
                    'description' => 'Diagnostico para desktop corporativo.',
                    'value' => 110.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'q-desktop-1',
                        'sequence' => 1,
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
                            ['key' => 'opt-gpu', 'label' => 'Placa de video', 'procedure_id' => ''],
                        ],
                        'answer_procedure_map_json' => [
                            'opt-ssd' => ['procedure_id' => ''],
                            'opt-ram' => ['procedure_id' => ''],
                            'opt-gpu' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
        ];

        ServiceOrderAnalysisService::createManyWithQuestions($items);
    }
}
