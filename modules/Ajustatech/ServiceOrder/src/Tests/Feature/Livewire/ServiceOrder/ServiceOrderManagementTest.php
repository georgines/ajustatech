<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisServiceFactory;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeModel;
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
        $analysisService = ServiceOrderAnalysisServiceFactory::new()->create([
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
                'procedure_id' => $analysisService->id,
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

    public function test_can_update_service_order_and_persists_changes_after_validation(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Cliente Edição',
        ]);

        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => 'Dell',
            'equipment_model' => 'Inspiron 15',
            'equipment_serial_number' => 'SN-0001',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->set('serviceOrderForm.equipment_model', 'Inspiron 16')
            ->set('serviceOrderForm.equipment_serial_number', 'SN-9999')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('service-order-list', ['serviceOrder' => $serviceOrder->id]));

        $this->assertDatabaseHas('service_order_bases', [
            'id' => $serviceOrder->id,
            'equipment_model' => 'Inspiron 16',
            'equipment_serial_number' => 'SN-9999',
        ]);
    }

    public function test_update_validates_required_order_fields_before_saving(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => 'Dell',
            'equipment_model' => 'Inspiron 15',
            'equipment_serial_number' => 'SN-0002',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->set('serviceOrderForm.equipment_model', '')
            ->call('save')
            ->assertHasErrors(['serviceOrderForm.equipment_model' => 'required']);

        $this->assertDatabaseHas('service_order_bases', [
            'id' => $serviceOrder->id,
            'equipment_model' => 'Inspiron 15',
            'equipment_serial_number' => 'SN-0002',
        ]);
    }

    public function test_renders_service_items_as_text_and_shows_subtotal(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $component = Livewire::test(ServiceOrderManagement::class)
            ->set('serviceItems', [[
                'procedure_id' => '',
                'item_name' => 'Troca de conector USB',
                'item_notes' => '',
                'unit_value' => '350.00',
                'discount_value' => '25.00',
                'total_value' => '325.00',
            ]]);

        $component
            ->assertSee('Troca de conector USB')
            ->assertSee('R$ 350,00')
            ->assertSee('R$ 25,00')
            ->assertSee('R$ 325,00')
            ->assertSee(trans('service-order::messages.subtotal'))
            ->assertSee('R$ 325,00');

        $component
            ->call('openDiscountModal', 0)
            ->assertSet('showDiscountModal', true)
            ->assertSet('discountItemIndex', 0)
            ->assertSet('discountInput', '25.00');
    }

    public function test_service_item_delete_requests_confirmation_before_removing(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $component = Livewire::test(ServiceOrderManagement::class)
            ->set('serviceItems', [[
                'procedure_id' => '',
                'item_name' => 'Limpeza interna',
                'item_notes' => '',
                'unit_value' => '120.00',
                'discount_value' => '20.00',
                'total_value' => '100.00',
            ]]);

        $component
            ->call('removeServiceItem', 0)
            ->assertDispatched('confirmation')
            ->assertSet('serviceItems.0.item_name', 'Limpeza interna');

        $component
            ->dispatch('service-order-remove-item', index: 0)
            ->assertSet('serviceItems', []);

    }

    public function test_list_of_services_comes_from_analysis_catalog_not_procedures(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $analysisService = ServiceOrderAnalysisServiceFactory::new()->create([
            'name' => 'Atualizacao de BIOS/UEFI',
            'value' => 149.90,
        ]);

        ServiceOrderProcedure::factory()->create([
            'name' => 'Troca de tela',
            'value' => 350.00,
        ]);

        Livewire::test(ServiceOrderManagement::class)
            ->set('serviceItems', [[
                'procedure_id' => '',
                'item_name' => '',
                'item_notes' => '',
                'unit_value' => '0.00',
                'discount_value' => '0.00',
                'total_value' => '0.00',
            ]])
            ->assertSee('Atualizacao de BIOS/UEFI')
            ->assertDontSee('Troca de tela');

        $this->assertDatabaseHas('service_order_analysis_services', [
            'id' => $analysisService->id,
            'name' => 'Atualizacao de BIOS/UEFI',
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
            'is_active' => true,
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

    public function test_can_add_brand_and_model_through_their_modals(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $existingBrand = ServiceOrderEquipmentTypeBrand::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'name' => 'Lenovo',
            'usage_count' => 4,
        ]);

        $otherBrand = ServiceOrderEquipmentTypeBrand::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'name' => 'Dell',
            'usage_count' => 8,
        ]);

        $existingModel = ServiceOrderEquipmentTypeModel::factory()->create([
            'equipment_type_brand_id' => $existingBrand->id,
            'name' => 'ThinkPad E14',
            'usage_count' => 6,
        ]);

        ServiceOrderEquipmentTypeModel::factory()->create([
            'equipment_type_brand_id' => $otherBrand->id,
            'name' => 'Inspiron 15',
            'usage_count' => 3,
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => 'Samsung',
            'equipment_model' => 'Book 2',
            'equipment_serial_number' => 'SN-001',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->call('openEquipmentBrandModal')
            ->assertSet('showEquipmentBrandModal', true)
            ->assertSet('equipmentBrandSearchActive', false)
            ->assertDontSee(trans('service-order::messages.equipment_brand_no_results'))
            ->set('equipmentBrandSearch', 'Lenovo')
            ->assertSee('Lenovo')
            ->call('selectEquipmentBrand', $existingBrand->id)
            ->assertSet('showEquipmentBrandModal', false)
            ->assertSet('serviceOrderForm.equipment_brand', 'Lenovo')
            ->assertSet('selectedEquipmentBrandId', $existingBrand->id)
            ->call('openEquipmentModelModal')
            ->assertSet('showEquipmentModelModal', true)
            ->assertSet('equipmentModelSearchActive', false)
            ->set('equipmentModelSearch', '')
            ->set('equipmentModelSearch', 'ThinkPad X1')
            ->assertSee('Nenhum resultado para esta busca')
            ->call('saveEquipmentModel')
            ->assertSet('showEquipmentModelModal', false)
            ->assertSet('serviceOrderForm.equipment_model', 'ThinkPad X1');

        $this->assertDatabaseHas('service_order_equipment_type_brands', [
            'equipment_type_id' => $equipmentType->id,
            'name' => 'Lenovo',
            'usage_count' => 5,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_models', [
            'equipment_type_brand_id' => $existingBrand->id,
            'name' => 'ThinkPad X1',
            'usage_count' => 1,
        ]);

        $this->assertDatabaseHas('service_order_equipment_type_models', [
            'equipment_type_brand_id' => $existingBrand->id,
            'name' => $existingModel->name,
        ]);
    }

    public function test_brand_and_model_lists_stay_empty_without_search_term(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
        ]);

        $brand = ServiceOrderEquipmentTypeBrand::factory()->create([
            'equipment_type_id' => $equipmentType->id,
            'name' => 'Lenovo',
        ]);

        ServiceOrderEquipmentTypeModel::factory()->create([
            'equipment_type_brand_id' => $brand->id,
            'name' => 'ThinkPad E14',
        ]);

        $serviceOrder = app(ServiceOrderServiceInterface::class)->createServiceOrder([
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'selected_document_id' => null,
            'equipment_brand' => '',
            'equipment_model' => '',
            'equipment_serial_number' => 'SN-002',
            'dynamic_fields' => [],
            'service_items' => [],
        ]);

        Livewire::test(ServiceOrderManagement::class, ['serviceOrder' => $serviceOrder])
            ->call('openEquipmentBrandModal')
            ->assertSet('showEquipmentBrandModal', true)
            ->assertDontSee(trans('service-order::messages.equipment_brand_no_results'))
            ->assertDontSee('Lenovo')
            ->call('openEquipmentModelModal')
            ->assertSet('showEquipmentModelModal', true)
            ->assertDontSee(trans('service-order::messages.equipment_model_no_results'))
            ->assertDontSee('ThinkPad E14');
    }

    public function test_can_change_equipment_type_through_modal_and_refresh_dependent_fields(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customer = Customer::factory()->create([
            'name' => 'Cliente Modal',
        ]);

        $currentEquipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Notebook',
            'is_active' => true,
        ]);

        $newEquipmentType = ServiceOrderEquipmentType::factory()->create([
            'name' => 'Celular',
            'is_active' => true,
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
