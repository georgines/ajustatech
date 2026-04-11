<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ServiceOrderManagement;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully(): void
    {
        Livewire::test(ServiceOrderManagement::class)
            ->assertStatus(200);
    }

    public function test_can_create_service_order_with_snapshot_dynamic_fields_and_services(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Cliente Snapshot',
        ]);

        $equipmentType = ServiceOrderEquipmentType::factory()->create();
        $document = ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
        ]);
        $field = ServiceOrderEquipmentTypeField::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'label' => 'Senha do equipamento',
            'is_required' => true,
        ]);
        $procedure = ServiceOrderProcedure::factory()->create([
            'name' => 'Troca de tela',
            'value' => 350.00,
        ]);

        Livewire::test(ServiceOrderManagement::class)
            ->set('serviceOrderForm.customer_id', $customer->id)
            ->set('serviceOrderForm.equipment_type_id', $equipmentType->id)
            ->set('serviceOrderForm.selected_document_id', $document->id)
            ->set('serviceOrderForm.equipment_brand', 'Dell')
            ->set('serviceOrderForm.equipment_model', 'Vostro 3400')
            ->set('serviceOrderForm.equipment_serial_number', 'SN-445566')
            ->set('dynamicFields', [[
                'equipment_type_field_id' => $field->id,
                'field_type' => 'text',
                'field_label' => 'Senha do equipamento',
                'field_placeholder' => 'Senha',
                'is_required' => true,
                'value_text' => '1234',
            ]])
            ->set('serviceItems', [[
                'procedure_id' => $procedure->id,
                'item_name' => 'Troca de tela',
                'item_notes' => 'Com pelicula',
                'unit_value' => '350.00',
                'discount_value' => '25.00',
                'total_value' => '325.00',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $serviceOrder = ServiceOrder::query()->latest('created_at')->firstOrFail();

        $this->assertSame($customer->id, $serviceOrder->customer_id);
        $this->assertSame('Cliente Snapshot', $serviceOrder->customer_snapshot_json['name']);
        $this->assertDatabaseHas('service_order_equipment_field_values', [
            'service_order_id' => $serviceOrder->id,
            'field_label' => 'Senha do equipamento',
            'value_text' => '1234',
        ]);
        $this->assertDatabaseHas('service_order_service_items', [
            'service_order_id' => $serviceOrder->id,
            'item_name' => 'Troca de tela',
            'unit_value' => '350.00',
            'discount_value' => '25.00',
            'total_value' => '325.00',
        ]);
    }

    public function test_renders_equipment_type_as_read_only_and_document_cards(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Cliente com Documento',
        ]);

        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $document = ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'title' => 'Termo de recebimento',
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => $document->id,
            'equipment_brand' => 'Samsung',
            'equipment_model' => 'VF-0697',
            'equipment_serial_number' => 'SN-30189445',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->assertSee('Editar tipo de equipamento')
            ->assertSee('Termo de recebimento')
            ->assertSee('Visualizar documento');
    }

    public function test_can_change_equipment_type_through_modal_and_refresh_dependent_fields(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Cliente Modal',
        ]);

        $currentEquipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $newEquipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Celular',
        ]);

        ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $currentEquipmentType->id,
            'title' => 'Laudo de notebook',
        ]);

        ServiceOrderEquipmentTypeDocument::factory()->create([
            'equipment_type_id' => $newEquipmentType->id,
            'title' => 'Ordem de celular',
        ]);

        ServiceOrderEquipmentTypeField::factory()->create([
            'equipment_type_id' => $newEquipmentType->id,
            'label' => 'IMEI',
            'placeholder' => 'Informe o IMEI',
            'default_text' => '000000000000000',
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $currentEquipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => 'Samsung',
            'equipment_model' => 'VF-0697',
            'equipment_serial_number' => 'SN-30189445',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->call('openEquipmentTypeModal')
            ->assertSet('showEquipmentTypeModal', true)
            ->set('equipmentTypeDraftId', $newEquipmentType->id)
            ->call('saveEquipmentType')
            ->assertSet('showEquipmentTypeModal', false)
            ->assertSet('serviceOrderForm.equipment_type_id', $newEquipmentType->id)
            ->assertSee('Celular')
            ->assertSee('Ordem de celular')
            ->assertSee('IMEI');
    }

    public function test_customer_update_event_refreshes_snapshot_without_changing_customer(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Nome Original',
        ]);

        $equipmentType = ServiceOrderEquipmentType::factory()->create();

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => null,
            'equipment_model' => null,
            'equipment_serial_number' => null,
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        $customer->update(['name' => 'Nome Atualizado']);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->call('openCustomerCorrectionModal')
            ->assertSet('showCustomerCorrectionModal', true)
            ->call('handleCustomerCorrectionSaved', $customer->id)
            ->assertSet('showCustomerCorrectionModal', false)
            ->assertDispatched('customer-correction-saved');

        $serviceOrder->refresh();

        $this->assertSame($customer->id, $serviceOrder->customer_id);
        $this->assertSame('Nome Atualizado', $serviceOrder->customer_snapshot_json['name']);
    }
}
