<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Database\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceOrderEquipmentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_equipment_type_with_documents_and_fields(): void
    {
        $equipmentType = ServiceOrderEquipmentType::createWithDetails(
            [
                'name' => 'Notebook',
                'description' => 'Equipamento portatil',
                'is_active' => true,
            ],
            [
                [
                    'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
                    'title' => 'Contrato notebook',
                    'template_content' => 'Contrato para {{dados_cliente}}',
                    'variables_json' => ['dados_cliente'],
                ],
            ],
            [
                [
                    'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                    'label' => 'Numero de serie',
                    'is_required' => true,
                ],
            ],
        );

        $this->assertDatabaseHas('service_order_equipment_types', [
            'id' => $equipmentType->id,
            'name' => 'Notebook',
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_documents', [
            'equipment_type_id' => $equipmentType->id,
            'title' => 'Contrato notebook',
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Numero de serie',
        ]);
    }

    public function test_delete_with_details_removes_related_files_and_rows(): void
    {
        Storage::fake('public');

        $pdfPath = 'service-order/equipment-types/documents/pdfs/notebook.pdf';
        $imagePath = 'service-order/equipment-types/fields/images/notebook.png';

        Storage::disk('public')->put($pdfPath, 'pdf-content');
        Storage::disk('public')->put($imagePath, 'image-content');

        $equipmentType = ServiceOrderEquipmentType::factory()->create();

        $document = ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
            'title' => 'Recibo notebook',
            'disk' => 'public',
            'path' => $pdfPath,
        ]);

        $field = ServiceOrderEquipmentTypeField::factory()->imageField()->create([
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Imagem frontal',
            'disk' => 'public',
            'path' => $imagePath,
        ]);

        $equipmentType = ServiceOrderEquipmentType::findWithDetailsOrFail($equipmentType->id);
        $equipmentType->deleteWithDetails();

        $this->assertDatabaseMissing('service_order_equipment_types', ['id' => $equipmentType->id]);
        $this->assertDatabaseMissing('service_order_equipment_type_documents', ['id' => $document->id]);
        $this->assertDatabaseMissing('service_order_equipment_type_fields', ['id' => $field->id]);
        Storage::disk('public')->assertMissing($pdfPath);
        Storage::disk('public')->assertMissing($imagePath);
    }
}
