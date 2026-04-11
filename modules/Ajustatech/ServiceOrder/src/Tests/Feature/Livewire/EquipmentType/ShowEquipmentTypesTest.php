<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\ShowEquipmentTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ShowEquipmentTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_equipment_types_list(): void
    {
        ServiceOrderEquipmentType::factory()->create([
            'name' => 'Computador desktop',
            'is_active' => true,
        ]);

        Livewire::test(ShowEquipmentTypes::class)
            ->assertStatus(200)
            ->assertSee('Computador desktop');
    }

    public function test_can_duplicate_equipment_type_with_documents_and_fields(): void
    {
        Storage::fake('public');

        $pdfPath = 'service-order/equipment-types/documents/pdfs/base.pdf';
        $imagePath = 'service-order/equipment-types/fields/images/base.png';

        Storage::disk('public')->put($pdfPath, 'pdf-content');
        Storage::disk('public')->put($imagePath, 'image-content');

        $equipmentType = ServiceOrderEquipmentType::factory()->create(['name' => 'Notebook']);

        ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
            'title' => 'Contrato notebook',
            'disk' => 'public',
            'path' => $pdfPath,
        ]);

        ServiceOrderEquipmentTypeField::factory()->imageField()->create([
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Imagem frontal',
            'disk' => 'public',
            'path' => $imagePath,
        ]);

        Livewire::test(ShowEquipmentTypes::class)
            ->call('duplicateEquipmentType', $equipmentType->id);

        $this->assertDatabaseHas('service_order_equipment_types', [
            'name' => 'Notebook (Copia)',
        ]);

        $clone = ServiceOrderEquipmentType::query()->where('name', 'Notebook (Copia)')->firstOrFail();

        $this->assertDatabaseCount('service_order_equipment_type_documents', 2);
        $this->assertDatabaseCount('service_order_equipment_type_fields', 2);

        $clonedDocument = ServiceOrderEquipmentTypeDocument::query()
            ->where('equipment_type_id', $clone->id)
            ->firstOrFail();

        $clonedField = ServiceOrderEquipmentTypeField::query()
            ->where('equipment_type_id', $clone->id)
            ->firstOrFail();

        $this->assertNotSame($pdfPath, $clonedDocument->path);
        $this->assertNotSame($imagePath, $clonedField->path);
        Storage::disk('public')->assertExists($clonedDocument->path);
        Storage::disk('public')->assertExists($clonedField->path);
    }

    public function test_can_delete_equipment_type_with_related_data(): void
    {
        $equipmentType = ServiceOrderEquipmentType::factory()->create();

        ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);

        ServiceOrderEquipmentTypeField::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);

        Livewire::test(ShowEquipmentTypes::class)
            ->call('deleteEquipmentType', $equipmentType->id);

        $this->assertDatabaseMissing('service_order_equipment_types', ['id' => $equipmentType->id]);
        $this->assertDatabaseMissing('service_order_equipment_type_documents', ['equipment_type_id' => $equipmentType->id]);
        $this->assertDatabaseMissing('service_order_equipment_type_fields', ['equipment_type_id' => $equipmentType->id]);
    }
}
