<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderEquipmentFieldValue;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderServiceItem;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\CreateServiceOrderWizard;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ShowServiceOrder;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_defaults_date_filters_to_current_month(): void
    {
        $now = Carbon::now();

        Livewire::test(ShowServiceOrder::class)
            ->assertSet('openedFrom', $now->copy()->startOfMonth()->format('Y-m-d'))
            ->assertSet('openedTo', $now->copy()->endOfMonth()->format('Y-m-d'));
    }

    public function test_initial_render_does_not_load_equipment_type_tables(): void
    {
        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        Livewire::test(ShowServiceOrder::class);

        $sql = implode("\n", $queries);

        $this->assertStringNotContainsString('service_order_equipment_type_documents', $sql);
        $this->assertStringNotContainsString('service_order_equipment_type_fields', $sql);
    }

    public function test_wizard_component_loads_equipment_types_only_when_opened(): void
    {
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'is_active' => true,
        ]);

        Livewire::test(CreateServiceOrderWizard::class)
            ->assertSet('showCreateWizardModal', false)
            ->assertSet('equipmentTypes', [])
            ->call('openWizard')
            ->assertSet('showCreateWizardModal', true)
            ->assertSee($equipmentType->name);
    }

    public function test_can_duplicate_service_order_with_related_items(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'is_active' => true,
        ]);
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
            ->assertSeeText(trans('service-order::messages.so_number'))
            ->assertSeeText(trans('service-order::messages.so_days'))
            ->assertSeeText(trans('service-order::messages.so_opened_at'))
            ->assertSeeText(trans('service-order::messages.so_customer'))
            ->assertSeeText(trans('service-order::messages.so_status'))
            ->assertSeeText(trans('service-order::messages.so_actions'))
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

    public function test_renders_expected_columns_and_vuexy_pagination_footer(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create();

        ServiceOrder::factory()
            ->count(11)
            ->create([
                'customer_id' => $customer->id,
                'equipment_type_id' => $equipmentType->id,
            ]);

        Livewire::test(ShowServiceOrder::class)
            ->assertSeeText(trans('service-order::messages.so_number'))
            ->assertSeeText(trans('service-order::messages.so_days'))
            ->assertSeeText(trans('service-order::messages.so_opened_at'))
            ->assertSeeText(trans('service-order::messages.so_customer'))
            ->assertSeeText(trans('service-order::messages.so_status'))
            ->assertSeeText(trans('service-order::messages.so_actions'))
            ->assertSeeText(trans('service-order::messages.pagination_showing_results', [
                'first' => 1,
                'last' => 10,
                'total' => 11,
            ]));
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

        Livewire::test(CreateServiceOrderWizard::class)
            ->call('openWizard')
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

        Livewire::test(CreateServiceOrderWizard::class)
            ->call('openWizard')
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
