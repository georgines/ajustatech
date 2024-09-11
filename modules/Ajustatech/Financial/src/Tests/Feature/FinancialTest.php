<?php

namespace Ajustatech\Financial\Tests\Feature;


use Ajustatech\Financial\Database\Models\Financial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTest extends TestCase
{
	use RefreshDatabase;

	public function test_record_is_created_successfully()
{
    $financial = Financial::factory()->create();
    $this->assertDatabaseHas('financials', [
        'id' => $financial->id,
    ]);
}
}
