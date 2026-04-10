<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\EquipmentTypeManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_equipment_type_with_documents_and_fields(): void
    {
        Livewire::test(EquipmentTypeManagement::class)
            ->set('name', 'Celular')
            ->set('description', 'Smartphones para reparo')
            ->set('isActive', true)
            ->set('pdfDocuments.0.title', 'Contrato celular')
            ->set('pdfDocuments.0.description', 'Contrato padrao para celulares')
            ->set('pdfDocuments.0.file', UploadedFile::fake()->create('contrato-celular.pdf', 150, 'application/pdf'))
            ->set('editableDocuments.0.title', 'Recibo celular')
            ->set('editableDocuments.0.content', 'Recibo para {{dados_cliente}} do aparelho {{equipamento_modelo}}')
            ->set('editableDocuments.0.variables', 'dados_cliente, equipamento_modelo')
            ->set('textFields.0.label', 'IMEI')
            ->set('textFields.0.placeholder', 'Informe o IMEI')
            ->set('textFields.0.is_required', true)
            ->set('imageFields.0.label', 'Foto frontal')
            ->call('save')
            ->assertRedirect(route('service-order-equipment-types-show'));

        $this->assertDatabaseHas('service_order_equipment_types', [
            'name' => 'Celular',
            'description' => 'Smartphones para reparo',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_documents', [
            'title' => 'Contrato celular',
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_documents', [
            'title' => 'Recibo celular',
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_fields', [
            'label' => 'IMEI',
            'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_fields', [
            'label' => 'Foto frontal',
            'field_type' => ServiceOrderEquipmentTypeField::TYPE_IMAGE,
        ]);
    }

    public function test_can_update_equipment_type_data(): void
    {
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Computador desktop',
        ]);

        ServiceOrderEquipmentTypeDocument::factory()->editableTemplate()->create([
            'equipment_type_id' => $equipmentType->id,
            'title' => 'Template antigo',
        ]);

        ServiceOrderEquipmentTypeField::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Campo antigo',
        ]);

        Livewire::test(EquipmentTypeManagement::class, ['id' => $equipmentType->id])
            ->set('name', 'Computador desktop gamer')
            ->set('description', 'Descricao atualizada')
            ->set('editableDocuments.0.title', 'Template novo')
            ->set('editableDocuments.0.content', 'Contrato atualizado para {{dados_cliente}}')
            ->set('textFields.0.label', 'Numero de serie atualizado')
            ->call('save')
            ->assertRedirect(route('service-order-equipment-types-show'));

        $this->assertDatabaseHas('service_order_equipment_types', [
            'id' => $equipmentType->id,
            'name' => 'Computador desktop gamer',
            'description' => 'Descricao atualizada',
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_documents', [
            'equipment_type_id' => $equipmentType->id,
            'title' => 'Template novo',
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_fields', [
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Numero de serie atualizado',
        ]);
    }

    public function test_duplicate_pdf_document_in_management_clones_file_path(): void
    {
        Storage::fake('public');

        $originalPath = 'service-order/equipment-types/documents/pdfs/original.pdf';
        Storage::disk('public')->put($originalPath, 'pdf-content');

        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
            'title' => 'Termo base',
            'disk' => 'public',
            'path' => $originalPath,
        ]);

        Livewire::test(EquipmentTypeManagement::class, ['id' => $equipmentType->id])
            ->call('duplicatePdfDocument', 0)
            ->call('save')
            ->assertRedirect(route('service-order-equipment-types-show'));

        $documents = ServiceOrderEquipmentTypeDocument::query()
            ->where('equipment_type_id', $equipmentType->id)
            ->where('document_type', ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF)
            ->orderBy('sort_order')
            ->get();

        $this->assertCount(2, $documents);
        $this->assertNotSame($documents[0]->path, $documents[1]->path);
        Storage::disk('public')->assertExists($documents[0]->path);
        Storage::disk('public')->assertExists($documents[1]->path);
    }
}
