<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowProceduresTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_procedure_list(): void
    {
        ServiceOrderProcedure::factory()->create([
            'name' => 'Formatacao',
            'value' => 129.90,
        ]);

        Livewire::test(ShowProcedures::class)
            ->assertStatus(200)
            ->assertSee('Formatacao');
    }

    public function test_can_delete_procedure(): void
    {
        $procedure = ServiceOrderProcedure::factory()->create();

        Livewire::test(ShowProcedures::class)
            ->call('deleteProcedure', $procedure->id);

        $this->assertDatabaseMissing('service_order_procedures', [
            'id' => $procedure->id,
        ]);
    }
}

