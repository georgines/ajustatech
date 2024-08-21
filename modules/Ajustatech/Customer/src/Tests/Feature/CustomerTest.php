<?php

namespace Ajustatech\Customer\Tests\Feature;


use Ajustatech\Customer\Database\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
	use RefreshDatabase;


	public function test_can_create_customer()
	{
		$customer = Customer::factory()->create();
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


	public function test_can_update_customer()
	{
		$customer = Customer::factory()->create();
		$customer2 = Customer::factory()->make();

		$customer->name = $customer2->name;
		$customer->person = $customer2->person;
		$customer->cpf_cnpj = $customer2->cpf_cnpj;
		$customer->cellphone = $customer2->cellphone;
		$customer->phone = $customer2->phone;
		$customer->email = $customer2->email;
		$customer->date_of_birth = $customer2->date_of_birth;
		$customer->zip_code = $customer2->zip_code;
		$customer->address = $customer2->address;
		$customer->number = $customer2->number;
		$customer->neighborhood = $customer2->neighborhood;
		$customer->city = $customer2->city;
		$customer->state = $customer2->state;
		$customer->complement = $customer2->complement;
		$customer->observations = $customer2->observations;
		$customer->status = $customer2->status;
		$customer->save();
		$this->assertDatabaseHas('customers', [
			'name' => $customer2->name,
			'person' => $customer2->person,
			'cpf_cnpj' => $customer2->cpf_cnpj,
			'cellphone' => $customer2->cellphone,
			'phone' => $customer2->phone,
			'email' => $customer2->email,
			'date_of_birth' => $customer2->date_of_birth,
			'zip_code' => $customer2->zip_code,
			'address' => $customer2->address,
			'number' => $customer2->number,
			'neighborhood' => $customer2->neighborhood,
			'city' => $customer2->city,
			'state' => $customer2->state,
			'complement' => $customer2->complement,
			'observations' => $customer2->observations,
			'status' => $customer2->status,
		]);
	}


	public function test_can_delete_customer()
	{
		$customer = Customer::factory()->create();
		$customer->delete();
		$this->assertDatabaseMissing('customers', [
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


	public function test_can_search_customer()
	{
		$customer = Customer::factory()->create(
			[
				'name' => 'John Doe',
				'status' => '1',
			]
		);
		Customer::factory()->create(
			[
				'name' => 'Mary Doe',
				'status' => '1',
			]
		);

		$results = Customer::search($customer->name, true);
		$this->assertCount(1, $results);
		$this->assertEquals($customer->name, $results->first()->name);
	}


	public function test_can_search_customer_with_limit()
	{
		Customer::factory(7)->create(
			[
				'name' => 'John Doe',
			]
		);
		$results = Customer::search('John Doe', false, 5);
		$this->assertCount(5, $results);
	}


	public function test_can_search_customer_with_status_zero()
	{
		$customer = Customer::factory()->create(
			[
				'name' => 'John Doe',
				'status' => '0',
			]
		);
		$customer2 = Customer::factory()->create(
			[
				'name' => 'Mary Doe',
				'status' => '1',
			]
		);

		$results = Customer::search('John Doe', false);
		$this->assertCount(1, $results);
		$this->assertEquals('John Doe', $results->first()->name);
	}


	public function test_can_search_customer_with_status_one()
	{
		$customer = Customer::factory()->create(
			[
				'name' => 'John Doe',
				'status' => '0',
			]
		);
		Customer::factory()->create(
			[
				'name' => 'Mary Doe',
				'status' => '1',
			]
		);

		$results = Customer::search('Doe', true);
		$this->assertCount(1, $results);
		$this->assertEquals('Mary Doe', $results->first()->name);
	}


	public function test_count_the_number_of_customers(): void
	{
		Customer::factory(7)->create(['status' => '1']);
		$results = Customer::searchAll(true);
		$this->assertCount(7, $results);
	}


	public function test_count_the_number_of_customers_with_limit(): void
	{
		Customer::factory(7)->create(['status' => '1']);
		$results = Customer::searchAll(true, 5);
		$this->assertCount(5, $results);
	}


	public function test_count_the_number_of_customers_with_status_zero(): void
	{
		Customer::factory(7)->create(['status' => '0']);
		$results = Customer::searchAll(false);
		$this->assertCount(7, $results);
	}


	public function test_count_the_number_of_customers_with_status_one(): void
	{
		Customer::factory(7)->create(['status' => '1']);
		Customer::factory(7)->create(['status' => '0']);
		$results = Customer::searchAll(true);
		$this->assertCount(7, $results);
	}
}
