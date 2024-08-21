<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('customers', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('person');
			$table->string('cpf_cnpj')->unique();
			$table->string('state_registration')->nullable();
			$table->string('rg')->nullable();
			$table->date('issue_date')->nullable();
			$table->string('issuer')->nullable();
			$table->string('cellphone')->nullable();
			$table->string('phone')->nullable();
			$table->string('email')->unique();
			$table->date('date_of_birth')->nullable();
			$table->string('marital_status')->nullable();
			$table->string('zip_code');
			$table->string('address');
			$table->string('number');
			$table->string('neighborhood');
			$table->string('city');
			$table->string('state');
			$table->string('birthplace')->nullable();
			$table->decimal('credit_limit', 10, 2)->nullable();
			$table->string('complement')->nullable();
			$table->string("fathers_name")->nullable();
			$table->string("mothers_name")->nullable();
			$table->text('observations')->nullable();
			$table->string('status')->default("1");
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('customers');
	}
};
