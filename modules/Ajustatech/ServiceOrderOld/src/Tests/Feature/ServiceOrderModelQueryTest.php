<?php

namespace Ajustatech\ServiceOrderOld\Tests\Feature;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderAttachment;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderFieldValue;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderModelQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_type_model_concentrates_selection_queries(): void
    {
        $inactive = EquipmentType::factory()->create(['name' => 'Zeta', 'is_active' => false]);
        $activeB = EquipmentType::factory()->create(['name' => 'Notebook', 'is_active' => true]);
        $activeA = EquipmentType::factory()->create(['name' => 'Celular', 'is_active' => true]);

        $selection = EquipmentType::getActiveSelectionList();
        $listing = EquipmentType::getListingWithFieldsCount();

        $this->assertSame([$activeA->id, $activeB->id], collect($selection)->pluck('id')->all());
        $this->assertContains($inactive->id, $listing->pluck('id')->all());
    }

    public function test_equipment_type_field_model_handles_slug_lookup_and_order_update(): void
    {
        $equipmentType = EquipmentType::factory()->create();
        $field = EquipmentTypeField::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'slug' => 'observacoes',
            'sort_order' => 1,
        ]);

        $this->assertTrue(EquipmentTypeField::slugExistsInEquipmentType($equipmentType->id, 'observacoes'));

        EquipmentTypeField::updateSortOrderWithinEquipmentType($equipmentType->id, $field->id, 3);

        $this->assertDatabaseHas('equipment_type_fields', [
            'id' => $field->id,
            'sort_order' => 3,
        ]);
    }

    public function test_service_catalog_model_exposes_reusable_and_active_steps_queries(): void
    {
        $active = ServiceCatalogService::factory()->create([
            'name' => 'Ativo',
            'is_active' => true,
            'is_reusable' => true,
        ]);
        $inactive = ServiceCatalogService::factory()->create([
            'name' => 'Inativo',
            'is_active' => false,
            'is_reusable' => true,
        ]);

        $active->steps()->createMany([
            ['name' => 'Passo 2', 'sort_order' => 2, 'is_required' => true, 'requires_image_proof' => false, 'is_active' => true],
            ['name' => 'Passo 1', 'sort_order' => 1, 'is_required' => true, 'requires_image_proof' => false, 'is_active' => true],
            ['name' => 'Passo oculto', 'sort_order' => 3, 'is_required' => false, 'requires_image_proof' => false, 'is_active' => false],
        ]);

        $selection = ServiceCatalogService::getReusableActiveSelectionList();
        $servicesById = ServiceCatalogService::getActiveWithActiveStepsByIds([$active->id, $inactive->id]);

        $this->assertSame([$active->id], collect($selection)->pluck('id')->all());
        $this->assertTrue($servicesById->has($active->id));
        $this->assertFalse($servicesById->has($inactive->id));
        $this->assertCount(2, $servicesById[$active->id]->steps);
        $this->assertSame('Passo 1', $servicesById[$active->id]->steps->first()->name);
    }

    public function test_service_order_model_concentrates_listing_and_document_queries(): void
    {
        $old = ServiceOrder::factory()->create(['created_at' => now()->subDay()]);
        $new = ServiceOrder::factory()->create([
            'fields_snapshot' => [
                ['name' => 'Termo', 'slug' => 'termo', 'field_type' => 'document', 'sort_order' => 2],
                ['name' => 'Obs', 'slug' => 'obs', 'field_type' => 'text', 'sort_order' => 1],
            ],
        ]);
        $new->fieldValues()->create([
            'field_slug' => 'termo',
            'field_type' => 'document',
            'value_text' => 'Texto pronto',
            'field_snapshot' => ['name' => 'Termo', 'slug' => 'termo', 'field_type' => 'document'],
        ]);

        $latest = ServiceOrder::getLatestListingWithServiceItems(2);
        $documents = ServiceOrder::findWithFieldValuesOrFail($new->id)->getDocumentFieldsWithValues();

        $this->assertSame([$new->id, $old->id], $latest->pluck('id')->all());
        $this->assertCount(1, $documents);
        $this->assertSame('Texto pronto', $documents[0]['value']);
    }

    public function test_field_value_and_attachment_models_handle_persistence_helpers(): void
    {
        $order = ServiceOrder::factory()->create();
        $fieldValue = ServiceOrderFieldValue::upsertForOrder($order->id, 'status_fisico', [
            'field_type' => 'text',
            'value_text' => 'Bom',
            'field_snapshot' => ['slug' => 'status_fisico'],
        ]);

        ServiceOrderFieldValue::upsertForOrder($order->id, 'status_fisico', [
            'field_type' => 'text',
            'value_text' => 'Excelente',
            'field_snapshot' => ['slug' => 'status_fisico'],
        ]);

        $attachment = ServiceOrderAttachment::createForOrder($order->id, [
            'field_slug' => 'foto_frontal',
            'attachment_type' => 'photo',
            'path' => 'service-orders/foto.jpg',
            'original_name' => 'foto.jpg',
            'extension' => 'jpg',
        ]);

        $this->assertSame('Excelente', $fieldValue->fresh()->value_text);
        $this->assertDatabaseHas('service_order_attachments', [
            'id' => $attachment->id,
            'service_order_id' => $order->id,
            'field_slug' => 'foto_frontal',
        ]);
    }

    public function test_service_order_uses_sequential_order_number_and_analysis_factory_snapshot_matches_type(): void
    {
        $first = ServiceOrder::factory()->create();
        $second = ServiceOrder::factory()->create();

        $this->assertNotNull($first->order_number);
        $this->assertSame($first->order_number + 1, $second->order_number);

        $analysisService = ServiceOrderAnalysisService::factory()->create();
        $this->assertSame($analysisService->analysis_type_id, data_get($analysisService->analysis_type_snapshot, 'id'));
    }
}
