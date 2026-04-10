<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $types = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Computador desktop',
                'description' => 'Equipamentos de bancada para uso residencial e corporativo.',
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
                        'title' => 'Contrato de manutencao desktop',
                        'description' => 'Template para abertura do servico em computadores desktop.',
                        'template_content' => 'Contrato firmado com {{dados_cliente}} para manutencao do equipamento {{equipamento_modelo}}. Numero da OS: {{numero_ordem_servico}}.',
                        'variables_json' => ['dados_cliente', 'equipamento_modelo', 'numero_ordem_servico'],
                    ],
                ],
                'fields' => [
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'Numero de serie',
                        'placeholder' => 'Ex: SN-CP-123456',
                        'default_text' => null,
                        'is_required' => true,
                    ],
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'Senha do sistema',
                        'placeholder' => 'Senha para testes de funcionamento',
                        'default_text' => null,
                        'is_required' => false,
                    ],
                ],
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Notebook',
                'description' => 'Equipamentos portateis para trabalho e estudo.',
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
                        'title' => 'Recibo de retirada notebook',
                        'description' => 'Recibo customizavel para retirada de notebook.',
                        'template_content' => 'Declaramos que {{dados_cliente}} retirou o equipamento {{equipamento_modelo}} em {{data_retirada}}.',
                        'variables_json' => ['dados_cliente', 'equipamento_modelo', 'data_retirada'],
                    ],
                ],
                'fields' => [
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'Marca e modelo',
                        'placeholder' => 'Ex: Dell Inspiron 15',
                        'default_text' => null,
                        'is_required' => true,
                    ],
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'Carregador incluso',
                        'placeholder' => 'Ex: Sim/nao + observacao',
                        'default_text' => null,
                        'is_required' => false,
                    ],
                ],
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Celular',
                'description' => 'Smartphones Android e iOS para reparo e manutencao.',
                'is_active' => true,
                'documents' => [
                    [
                        'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
                        'title' => 'Autorizacao de servico celular',
                        'description' => 'Documento de autorizacao para manutencao em smartphones.',
                        'template_content' => 'Eu, {{dados_cliente}}, autorizo a manutencao do aparelho {{equipamento_modelo}} IMEI {{imei}}.',
                        'variables_json' => ['dados_cliente', 'equipamento_modelo', 'imei'],
                    ],
                ],
                'fields' => [
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'IMEI',
                        'placeholder' => 'Ex: 358240051111110',
                        'default_text' => null,
                        'is_required' => true,
                    ],
                    [
                        'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                        'label' => 'Senha ou padrao de desbloqueio',
                        'placeholder' => 'Informar somente para testes',
                        'default_text' => null,
                        'is_required' => false,
                    ],
                ],
            ],
        ];

        $typeRows = [];
        $documentRows = [];
        $fieldRows = [];

        foreach ($types as $typeIndex => $type) {
            $typeRows[] = [
                'id' => $type['id'],
                'name' => $type['name'],
                'description' => $type['description'],
                'is_active' => $type['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach (($type['documents'] ?? []) as $documentIndex => $document) {
                $documentRows[] = [
                    'id' => (string) Str::uuid(),
                    'equipment_type_id' => $type['id'],
                    'document_type' => $document['document_type'],
                    'title' => $document['title'],
                    'description' => $document['description'],
                    'template_content' => $document['template_content'] ?? null,
                    'variables_json' => json_encode($document['variables_json'] ?? []),
                    'disk' => null,
                    'path' => null,
                    'original_name' => null,
                    'mime_type' => null,
                    'extension' => null,
                    'size' => null,
                    'sort_order' => ($typeIndex * 100) + $documentIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (($type['fields'] ?? []) as $fieldIndex => $field) {
                $fieldRows[] = [
                    'id' => (string) Str::uuid(),
                    'equipment_type_id' => $type['id'],
                    'field_type' => $field['field_type'],
                    'label' => $field['label'],
                    'placeholder' => $field['placeholder'] ?? null,
                    'default_text' => $field['default_text'] ?? null,
                    'is_required' => $field['is_required'] ?? false,
                    'disk' => null,
                    'path' => null,
                    'original_name' => null,
                    'mime_type' => null,
                    'extension' => null,
                    'size' => null,
                    'sort_order' => ($typeIndex * 100) + $fieldIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        ServiceOrderEquipmentType::query()->insert($typeRows);

        if (! empty($documentRows)) {
            ServiceOrderEquipmentTypeDocument::query()->insert($documentRows);
        }

        if (! empty($fieldRows)) {
            ServiceOrderEquipmentTypeField::query()->insert($fieldRows);
        }
    }
}
