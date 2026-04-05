<?php

namespace Ajustatech\ServiceOrderOld\Tests\Feature;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Livewire\NewServiceOrderManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewServiceOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_customer_before_proceeding_to_order_step(): void
    {
        Livewire::test(NewServiceOrderManagement::class)
            ->assertSet('currentStep', 'customer')
            ->assertSet('customer_id', null)
            ->call('proceedToOrder')
            ->assertSet('currentStep', 'customer')
            ->assertHasErrors(['customer']);
    }

    public function test_can_select_existing_customer_and_proceed(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Maria Oliveira',
        ]);

        Livewire::test(NewServiceOrderManagement::class)
            ->set('customerSearch', 'Maria')
            ->call('selectCustomer', $customer->id)
            ->assertSet('customer_id', $customer->id)
            ->call('proceedToOrder')
            ->assertSet('currentStep', 'order');
    }

    public function test_customer_created_event_selects_customer_and_enables_flow(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Cliente Novo',
        ]);

        Livewire::test(NewServiceOrderManagement::class)
            ->set('showCustomerModal', true)
            ->dispatch('customer-created', id: $customer->id, name: $customer->name)
            ->assertSet('showCustomerModal', false)
            ->assertSet('customer_id', $customer->id)
            ->assertSet('customerSearch', $customer->name)
            ->assertSet('currentStep', 'customer')
            ->call('proceedToOrder')
            ->assertSet('currentStep', 'order');
    }

    public function test_can_load_and_edit_existing_service_order_using_same_form(): void
    {
        $customer = Customer::factory()->create(['name' => 'Cliente Teste']);
        $equipmentType = EquipmentType::factory()->create(['name' => 'Notebook']);
        $order = ServiceOrder::factory()->create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'equipment_type_id' => $equipmentType->id,
            'equipment_name' => 'Notebook',
            'brand' => 'Marca antiga',
            'model' => 'Modelo antigo',
            'fields_snapshot' => [],
            'equipment_type_snapshot' => [
                'id' => $equipmentType->id,
                'name' => $equipmentType->name,
                'description' => $equipmentType->description,
            ],
        ]);

        Livewire::test(NewServiceOrderManagement::class, ['id' => $order->id])
            ->assertSet('isEditMode', true)
            ->assertSet('orderId', $order->id)
            ->set('brand', 'Marca nova')
            ->set('model', 'Modelo novo')
            ->call('save');

        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'brand' => 'Marca nova',
            'model' => 'Modelo novo',
        ]);
    }
}
