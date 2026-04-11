<?php

namespace Ajustatech\ServiceOrderOld\Database\Seeders;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Services\EquipmentTypeService;
use Illuminate\Database\Seeder;

class EquipmentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(EquipmentTypeService::class);

        foreach ($this->defaults() as $payload) {
            $existing = EquipmentType::query()->where('name', $payload['name'])->first();

            if ($existing) {
                $service->update($existing->id, $payload);
                continue;
            }

            $service->create($payload);
        }
    }

    private function defaults(): array
    {
        return [
            [
                'name' => 'Notebook',
                'description' => 'Cadastro padrao para equipamentos de notebook.',
                'is_active' => true,
                'fields' => [
                    [
                        'field_type' => 'photo',
                        'name' => 'Foto frontal do equipamento',
                        'slug' => 'foto_frontal_equipamento',
                        'sort_order' => 1,
                        'is_required' => true,
                        'is_printable' => false,
                        'is_active' => true,
                        'configuration' => [
                            'max_files' => 1,
                            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                        ],
                    ],
                    [
                        'field_type' => 'photo',
                        'name' => 'Foto da etiqueta serial',
                        'slug' => 'foto_etiqueta_serial',
                        'sort_order' => 2,
                        'is_required' => true,
                        'is_printable' => false,
                        'is_active' => true,
                        'configuration' => [
                            'max_files' => 2,
                            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                        ],
                    ],
                    [
                        'field_type' => 'text',
                        'name' => 'Observacoes de entrada',
                        'slug' => 'observacoes_entrada',
                        'sort_order' => 3,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => [
                            'placeholder' => 'Registre detalhes importantes na entrada.',
                            'help' => 'Inclua riscos, amassados, faltas de acessorios.',
                            'max_length' => 1000,
                        ],
                    ],
                    [
                        'field_type' => 'select',
                        'name' => 'Estado da carcaca',
                        'slug' => 'estado_carcaca',
                        'sort_order' => 4,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => [],
                        'options' => [
                            ['label' => 'Bom', 'value' => 'bom', 'sort_order' => 1, 'is_active' => true],
                            ['label' => 'Regular', 'value' => 'regular', 'sort_order' => 2, 'is_active' => true],
                            ['label' => 'Ruim', 'value' => 'ruim', 'sort_order' => 3, 'is_active' => true],
                        ],
                    ],
                    [
                        'field_type' => 'radio',
                        'name' => 'Acompanha carregador',
                        'slug' => 'acompanha_carregador',
                        'sort_order' => 5,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => [],
                        'options' => [
                            ['label' => 'Sim', 'value' => 'sim', 'sort_order' => 1, 'is_active' => true],
                            ['label' => 'Nao', 'value' => 'nao', 'sort_order' => 2, 'is_active' => true],
                        ],
                    ],
                    [
                        'field_type' => 'document',
                        'name' => 'Termo de entrada',
                        'slug' => 'termo_entrada',
                        'sort_order' => 6,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => [
                            'template' => 'Cliente: {{cliente_nome}} | Equipamento: {{equipamento_nome}} | Serie: {{numero_serie}} | Entrada: {{data_entrada}}',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Desktop',
                'description' => 'Cadastro padrao para desktop e all-in-one.',
                'is_active' => true,
                'fields' => [
                    [
                        'field_type' => 'text',
                        'name' => 'Defeito relatado no balcão',
                        'slug' => 'defeito_relato_balcao',
                        'sort_order' => 1,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => ['max_length' => 1200],
                    ],
                    [
                        'field_type' => 'file',
                        'name' => 'Anexo de comprovante',
                        'slug' => 'anexo_comprovante',
                        'sort_order' => 2,
                        'is_required' => false,
                        'is_printable' => false,
                        'is_active' => true,
                        'configuration' => [
                            'allowed_extensions' => ['pdf', 'jpg', 'png'],
                            'preview_mode' => 'new_tab',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Celular',
                'description' => 'Cadastro padrao para smartphones.',
                'is_active' => true,
                'fields' => [
                    [
                        'field_type' => 'photo',
                        'name' => 'Foto da tela ligada',
                        'slug' => 'foto_tela_ligada',
                        'sort_order' => 1,
                        'is_required' => true,
                        'is_printable' => false,
                        'is_active' => true,
                        'configuration' => [
                            'max_files' => 2,
                            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                        ],
                    ],
                    [
                        'field_type' => 'radio',
                        'name' => 'Aparelho liga',
                        'slug' => 'aparelho_liga',
                        'sort_order' => 2,
                        'is_required' => true,
                        'is_printable' => true,
                        'is_active' => true,
                        'configuration' => [],
                        'options' => [
                            ['label' => 'Sim', 'value' => 'sim', 'sort_order' => 1, 'is_active' => true],
                            ['label' => 'Nao', 'value' => 'nao', 'sort_order' => 2, 'is_active' => true],
                        ],
                    ],
                ],
            ],
        ];
    }
}

