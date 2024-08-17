<?php

namespace Ajustatech\Customer\Tests\Feature\Customer;

use Ajustatech\Customer\Livewire\ShowCustomer;
use Ajustatech\Customer\Database\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowCustomerTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(ShowCustomer::class)
			->assertStatus(200);
	}


	public function test_saech_for_customer(): void
	{
		$customer = Customer::factory()->create([
			'name' => 'maria dasdores',
			'email' => 'maria@gmail.com',
			'status' => '1'
		]);

		Livewire::test(ShowCustomer::class)
			->set('search', 'dasd')
			->assertSee($customer->fresh()->name)
			->assertSee($customer->fresh()->email);
	}


	public function test_count_the_number_of_customers(): void
	{
		Customer::factory(7)->create(['status' => '1']);
		Livewire::test(ShowCustomer::class)->assertCount('customers', 7);
	}


	public function test_can_filter_customers_by_name()
	{
		$customer1 = Customer::factory()->create(['name' => 'John Doe', 'status' => '1']);
		$customer2 = Customer::factory()->create(['name' => 'Jane Doe', 'status' => '1']);
		$customer3 = Customer::factory()->create(['name' => 'Bob Smith', 'status' => '1']);

		Livewire::test(ShowCustomer::class)
			->set('search', 'Doe')
			->assertSeeHtml($customer1->name)
			->assertSeeHtml($customer2->name)
			->assertDontSeeHtml($customer3->name);
	}


	public function test_does_not_show_inactive_customers()
	{
		$activeCustomer = Customer::factory()->create(['status' => '1']);
		$inactiveCustomer = Customer::factory()->create(['status' => '0']);

		Livewire::test(ShowCustomer::class)
			->assertSeeHtml($activeCustomer->name)
			->assertDontSeeHtml($inactiveCustomer->name);
	}


	public function test_shows_both_active_and_inactive_customers()
	{
		$activeCustomer = Customer::factory()->create(['status' => '1']);
		$inactiveCustomer = Customer::factory()->create(['status' => '0']);

		Livewire::test(ShowCustomer::class)
			->set('activeonly', false)
			->assertSeeHtml($activeCustomer->name)
			->assertSeeHtml($inactiveCustomer->name);
	}


	public function test_can_change_customer_status()
	{
		$customer = Customer::factory()->create(['status' => '1']);

		Livewire::test(ShowCustomer::class)
			->call('confirmChangeStatus', $customer->id)
			->assertDispatched('confirm-status')
			->dispatch('change-status', id: $customer->id);

		$this->assertEquals('0', $customer->fresh()->status);
	}

}
