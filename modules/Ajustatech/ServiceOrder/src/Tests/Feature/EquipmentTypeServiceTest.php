<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EquipmentTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_equipment_type_with_dynamic_fields(): void
    {
        $service = app(EquipmentTypeService::class);

        $equipmentType = $service->create([
            'name' => 'Notebook',
            'description' => 'Tipo para notebooks',
            'is_active' => true,
            'fields' => [
                [
                    'field_type' => 'text',
                    'name' => 'Observacoes',
                    'slug' => 'observacoes',
                    'sort_order' => 1,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => ['max_length' => 200],
                ],
                [
                    'field_type' => 'radio',
                    'name' => 'Acompanha carregador',
                    'slug' => 'acompanha_carregador',
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
        ]);

        $this->assertDatabaseHas('equipment_types', [
            'id' => $equipmentType->id,
            'name' => 'Notebook',
        ]);
        $this->assertDatabaseHas('equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'slug' => 'observacoes',
            'field_type' => 'text',
        ]);
        $this->assertDatabaseHas('equipment_type_field_options', [
            'label' => 'Sim',
            'value' => 'sim',
        ]);
    }

    public function test_can_reorder_equipment_type_fields_manually(): void
    {
        $service = app(EquipmentTypeService::class);
        $equipmentType = $service->create($this->notebookPayload());
        $fieldIds = $equipmentType->fields()->orderBy('sort_order')->pluck('id')->all();

        $service->reorderFields($equipmentType->id, array_reverse($fieldIds));

        $ordered = $equipmentType->fresh('fields')->fields->sortBy('sort_order')->pluck('id')->all();
        $this->assertSame(array_reverse($fieldIds), $ordered);
    }

    public function test_photo_and_file_fields_cannot_be_printable(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(EquipmentTypeService::class);
        $payload = $this->notebookPayload();
        $payload['fields'][0] = [
            'field_type' => 'photo',
            'name' => 'Foto frontal',
            'slug' => 'foto_frontal',
            'sort_order' => 1,
            'is_required' => true,
            'is_printable' => true,
            'is_active' => true,
            'configuration' => ['max_files' => 1, 'allowed_extensions' => ['jpg']],
        ];

        $service->create($payload);
    }

    public function test_slug_must_be_unique_per_equipment_type(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(EquipmentTypeService::class);
        $payload = $this->notebookPayload();
        $payload['fields'][1]['slug'] = 'observacoes';

        $service->create($payload);
    }

    public function test_select_and_radio_fields_must_define_options(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(EquipmentTypeService::class);
        $payload = $this->notebookPayload();
        $payload['fields'][1]['options'] = [];

        $service->create($payload);
    }

    public function test_document_field_rejects_invalid_variables(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(EquipmentTypeService::class);
        $payload = $this->notebookPayload();
        $payload['fields'][] = [
            'field_type' => 'document',
            'name' => 'Termo',
            'slug' => 'termo',
            'sort_order' => 3,
            'is_required' => true,
            'is_printable' => true,
            'is_active' => true,
            'configuration' => [
                'template' => 'Variavel invalida {{foo_bar}}',
            ],
        ];

        $service->create($payload);
    }

    private function notebookPayload(): array
    {
        return [
            'name' => 'Notebook',
            'description' => 'Cadastro para notebook',
            'is_active' => true,
            'fields' => [
                [
                    'field_type' => 'text',
                    'name' => 'Observacoes',
                    'slug' => 'observacoes',
                    'sort_order' => 1,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => ['max_length' => 200],
                ],
                [
                    'field_type' => 'select',
                    'name' => 'Estado',
                    'slug' => 'estado',
                    'sort_order' => 2,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [],
                    'options' => [
                        ['label' => 'Bom', 'value' => 'bom', 'sort_order' => 1, 'is_active' => true],
                        ['label' => 'Ruim', 'value' => 'ruim', 'sort_order' => 2, 'is_active' => true],
                    ],
                ],
            ],
        ];
    }
}
