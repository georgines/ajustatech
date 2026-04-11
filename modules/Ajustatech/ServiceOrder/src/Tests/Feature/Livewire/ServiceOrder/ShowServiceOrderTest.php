<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderEquipmentFieldValue;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderServiceItem;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ShowServiceOrder;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully(): void
    {
        Livewire::test(ShowServiceOrder::class)
            ->assertStatus(200);
    }

    public function test_can_duplicate_service_order_with_related_items(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create();
        $field = ServiceOrderEquipmentTypeField::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);
        $procedure = ServiceOrderProcedure::factory()->create();

        $serviceOrder = ServiceOrder::factory()->create([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'equipment_model' => 'Inspiron 15',
        ]);

        ServiceOrderEquipmentFieldValue::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'equipment_type_field_id' => $field->id,
            'field_label' => 'Defeito relatado',
            'value_text' => 'Sem imagem',
        ]);

        ServiceOrderServiceItem::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'procedure_id' => $procedure->id,
            'item_name' => 'Diagnostico inicial',
            'unit_value' => 100,
            'discount_value' => 10,
            'total_value' => 90,
        ]);

        Livewire::test(ShowServiceOrder::class)
            ->call('duplicateServiceOrder', $serviceOrder->id);

        $this->assertDatabaseCount('service_order_bases', 2);

        $copy = ServiceOrder::query()->where('id', '!=', $serviceOrder->id)->firstOrFail();

        $this->assertNotSame($serviceOrder->order_number, $copy->order_number);
        $this->assertDatabaseHas('service_order_equipment_field_values', [
            'service_order_id' => $copy->id,
            'field_label' => 'Defeito relatado',
        ]);
        $this->assertDatabaseHas('service_order_service_items', [
            'service_order_id' => $copy->id,
            'item_name' => 'Diagnostico inicial',
        ]);
    }

    public function test_can_delete_service_order_and_cascade_related_items(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $serviceOrder = ServiceOrder::factory()->create();
        ServiceOrderEquipmentFieldValue::factory()->create([
            'service_order_id' => $serviceOrder->id,
        ]);
        ServiceOrderServiceItem::factory()->create([
            'service_order_id' => $serviceOrder->id,
        ]);

        Livewire::test(ShowServiceOrder::class)
            ->call('deleteServiceOrder', $serviceOrder->id);

        $this->assertDatabaseMissing('service_order_bases', ['id' => $serviceOrder->id]);
        $this->assertDatabaseMissing('service_order_equipment_field_values', ['service_order_id' => $serviceOrder->id]);
        $this->assertDatabaseMissing('service_order_service_items', ['service_order_id' => $serviceOrder->id]);
    }

    public function test_sets_created_customer_as_selected_in_wizard(): void
    {
        $customer = Customer::factory()->create();

        Livewire::test(ShowServiceOrder::class)
            ->call('openCreateWizard')
            ->call('openCreateWizardCustomerCreateModal')
            ->call('handleCustomerCreated', $customer->id)
            ->assertSet('createWizard.customer_id', $customer->id)
            ->assertSet('createWizardCustomerSelectedId', $customer->id)
            ->assertSet('showCreateWizardCustomerCreateModal', false)
            ->call('openCreateWizardCustomerSelectModal')
            ->assertSet('createWizardCustomerSelectedId', $customer->id);
    }

    public function test_confirm_create_wizard_creates_empty_service_order_and_sets_opened_metadata(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create();

        Livewire::test(ShowServiceOrder::class)
            ->call('openCreateWizard')
            ->set('createWizard.equipment_type_id', $equipmentType->id)
            ->set('createWizard.customer_id', $customer->id)
            ->call('confirmCreateWizard');

        $this->assertDatabaseCount('service_order_bases', 1);

        $created = ServiceOrder::query()->with('statusFlow')->firstOrFail();

        $this->assertSame($customer->id, $created->customer_id);
        $this->assertSame($equipmentType->id, $created->equipment_type_id);
        $this->assertNotNull($created->order_number);
        $this->assertNotNull($created->opened_at);
        $this->assertNull($created->finished_at);
        $this->assertSame('entrada', $created->statusFlow?->code);
    }
}
