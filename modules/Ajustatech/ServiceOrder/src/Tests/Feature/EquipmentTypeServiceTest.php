<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_can_create_equipment_type_with_all_supported_field_configurations(): void
    {
        $service = app(EquipmentTypeService::class);

        $equipmentType = $service->create([
            'name' => 'Console',
            'description' => 'Cadastro completo com todos os tipos de campo',
            'is_active' => true,
            'fields' => [
                [
                    'field_type' => 'text',
                    'name' => 'Defeito detalhado',
                    'slug' => 'defeito_detalhado',
                    'sort_order' => 1,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => ['placeholder' => 'Descreva', 'help' => 'Detalhes do problema', 'max_length' => 1200],
                ],
                [
                    'field_type' => 'select',
                    'name' => 'Estado externo',
                    'slug' => 'estado_externo',
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
                [
                    'field_type' => 'radio',
                    'name' => 'Liga normalmente',
                    'slug' => 'liga_normalmente',
                    'sort_order' => 3,
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
                    'field_type' => 'photo',
                    'name' => 'Foto frontal',
                    'slug' => 'foto_frontal',
                    'sort_order' => 4,
                    'is_required' => true,
                    'is_printable' => false,
                    'is_active' => true,
                    'configuration' => ['max_files' => 3, 'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp']],
                ],
                [
                    'field_type' => 'file',
                    'name' => 'Laudo anterior',
                    'slug' => 'laudo_anterior',
                    'sort_order' => 5,
                    'is_required' => false,
                    'is_printable' => false,
                    'is_active' => true,
                    'configuration' => ['allowed_extensions' => ['pdf'], 'preview_mode' => 'modal'],
                ],
                [
                    'field_type' => 'document',
                    'name' => 'Termo de recebimento',
                    'slug' => 'termo_recebimento',
                    'sort_order' => 6,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => ['template' => 'Cliente: {{cliente_nome}} / Equipamento: {{equipamento_nome}}'],
                ],
            ],
        ]);

        $this->assertDatabaseHas('equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'slug' => 'foto_frontal',
            'field_type' => 'photo',
        ]);
        $this->assertDatabaseHas('equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'slug' => 'laudo_anterior',
            'field_type' => 'file',
        ]);
        $this->assertDatabaseHas('equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'slug' => 'termo_recebimento',
            'field_type' => 'document',
        ]);
    }

    #[DataProvider('imageDiskProvider')]
    public function test_stores_equipment_type_image_on_configured_disk(string $disk): void
    {
        Storage::fake($disk);

        config()->set('filesystems.default', $disk);
        config()->set('service_order.equipment_type_images.disk', $disk);
        config()->set('service_order.equipment_type_images.directory', 'equipment-types');
        config()->set('service_order.equipment_type_images.visibility', 'private');

        $service = app(EquipmentTypeService::class);
        $image = UploadedFile::fake()->image('notebook.png', 500, 500)->size(1024);

        $equipmentType = $service->create($this->notebookPayload(), $image);

        $this->assertSame($disk, $equipmentType->image_disk);
        $this->assertNotEmpty($equipmentType->image_path);
        $this->assertStringStartsWith('equipment-types/' . $equipmentType->id . '/', $equipmentType->image_path);
        Storage::disk($disk)->assertExists($equipmentType->image_path);
    }

    public function test_rejects_invalid_file_as_equipment_type_image(): void
    {
        $this->expectException(ValidationException::class);

        Storage::fake('public');
        config()->set('service_order.equipment_type_images.disk', 'public');

        $service = app(EquipmentTypeService::class);
        $invalidUpload = UploadedFile::fake()->create('script.php', 20, 'text/x-php');

        $service->create($this->notebookPayload(), $invalidUpload);
    }

    public function test_can_replace_and_remove_equipment_type_image(): void
    {
        Storage::fake('public');

        config()->set('service_order.equipment_type_images.disk', 'public');
        config()->set('service_order.equipment_type_images.directory', 'equipment-types');

        $service = app(EquipmentTypeService::class);

        $firstImage = UploadedFile::fake()->image('first.jpg');
        $equipmentType = $service->create($this->notebookPayload(), $firstImage);

        $oldPath = (string) $equipmentType->image_path;
        Storage::disk('public')->assertExists($oldPath);

        $replacement = UploadedFile::fake()->image('replacement.png');
        $updated = $service->update($equipmentType->id, $this->notebookPayload(), $replacement);

        $this->assertNotSame($oldPath, $updated->image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists((string) $updated->image_path);

        $removed = $service->update($equipmentType->id, $this->notebookPayload(), null, true);
        $this->assertNull($removed->image_path);
        $this->assertNull($removed->image_disk);
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

    public static function imageDiskProvider(): array
    {
        return [
            'public' => ['public'],
            'local' => ['local'],
            's3' => ['s3'],
        ];
    }
}
