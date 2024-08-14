<?php

namespace Ajustatech\Customer\Tests\Feature\Customer;

use Ajustatech\Customer\Http\Livewire\CustomerManagement;
use Ajustatech\Customer\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function renders_successfully()
	{
		Livewire::test(CustomerManagement::class)
			->assertStatus(200);
	}

	/** @test */
	//crie testes para verificar se a propriedade mode não está sendo alterada pelo usuário
	public function mode_property_is_locked()
	{
		Livewire::test(CustomerManagement::class)
			->assertSet('mode', 'create');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Cannot update locked property: [mode]');

		Livewire::test(CustomerManagement::class)
			->set('mode', 'edit');
	}

	/** @test */
	public function required_fields()
	{
		Livewire::test(CustomerManagement::class)
			->call('save')
			->assertHasErrors([
				'customer_.name' => 'required',
				'customer_.cpf_cnpj' => 'required',
				'customer_.cellphone' => 'required',
				'customer_.email' => 'required',
				'customer_.date_of_birth' => 'required',
				'customer_.zip_code' => 'required',
				'customer_.address' => 'required',
				'customer_.neighborhood' => 'required',
				'customer_.city' => 'required',
				'customer_.state' => 'required',
			]);
	}

	/** @test */
	public function save_customer()
	{
		$customer = Customer::factory()->make();

		Livewire::test(CustomerManagement::class)
			->set('customer_.name', $customer->name)
			->set('customer_.person', $customer->person)
			->set('customer_.cpf_cnpj', $customer->cpf_cnpj)
			->set('customer_.cellphone', $customer->cellphone)
			->set('customer_.phone', $customer->phone)
			->set('customer_.email', $customer->email)
			->set('customer_.date_of_birth', $customer->date_of_birth)
			->set('customer_.zip_code', $customer->zip_code)
			->set('customer_.address', $customer->address)
			->set('customer_.number', $customer->number)
			->set('customer_.neighborhood', $customer->neighborhood)
			->set('customer_.city', $customer->city)
			->set('customer_.state', $customer->state)
			->set('customer_.complement', $customer->complement)
			->set('customer_.observations', $customer->observations)
			->set('customer_.status', $customer->status)
			->call('save')
			->assertHasNoErrors();
		// ->assertRedirect(route('customers.index'));

		$this->assertDatabaseHas('customers', [
			'name' => $customer->name,
			'person' => $customer->person,
			'cpf_cnpj' => $customer->cpf_cnpj,
			'cellphone' => $customer->cellphone,
			'phone' => $customer->phone,
			'email' => $customer->email,
			'date_of_birth' => $customer->date_of_birth,
			'zip_code' => $customer->zip_code,
			'address' => $customer->address,
			'number' => $customer->number,
			'neighborhood' => $customer->neighborhood,
			'city' => $customer->city,
			'state' => $customer->state,
			'complement' => $customer->complement,
			'observations' => $customer->observations,
			'status' => $customer->status,
		]);
	}

	/** @test */
	public function update_customer()
	{
		$customer = Customer::factory()->create();

		$customerNew = Customer::factory()->make();

		Livewire::test(CustomerManagement::class, ['customer' => $customer])
			->set('customer_.name', $customerNew->name)
			->set('customer_.person', $customerNew->person)
			->set('customer_.cpf_cnpj', $customerNew->cpf_cnpj)
			->set('customer_.cellphone', $customerNew->cellphone)
			->set('customer_.phone', $customerNew->phone)
			->set('customer_.email', $customerNew->email)
			->set('customer_.date_of_birth', $customerNew->date_of_birth)
			->set('customer_.zip_code', $customerNew->zip_code)
			->set('customer_.address', $customerNew->address)
			->set('customer_.number', $customerNew->number)
			->set('customer_.neighborhood', $customerNew->neighborhood)
			->set('customer_.city', $customerNew->city)
			->set('customer_.state', $customerNew->state)
			->set('customer_.complement', $customerNew->complement)
			->set('customer_.observations', $customerNew->observations)
			->set('customer_.status', $customerNew->status)
			->call('save')
			->assertHasNoErrors();
		// ->assertRedirect(route('customers.index'));

		$this->assertDatabaseHas('customers', [
			'name' => $customerNew->name,
			'person' => $customerNew->person,
			'cpf_cnpj' => $customerNew->cpf_cnpj,
			'cellphone' => $customerNew->cellphone,
			'phone' => $customerNew->phone,
			'email' => $customerNew->email,
			'date_of_birth' => $customerNew->date_of_birth,
			'zip_code' => $customerNew->zip_code,
			'address' => $customerNew->address,
			'number' => $customerNew->number,
			'neighborhood' => $customerNew->neighborhood,
			'city' => $customerNew->city,
			'state' => $customerNew->state,
			'complement' => $customerNew->complement,
			'observations' => $customerNew->observations,
			'status' => $customerNew->status,
		]);
	}
}
