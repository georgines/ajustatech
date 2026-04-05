<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcedureManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_procedure(): void
    {
        Livewire::test(ProcedureManagement::class)
            ->set('name', 'Limpeza interna')
            ->set('description', 'Limpeza de poeira e conectores')
            ->set('value', '149.90')
            ->set('helpText', 'Use pincel antiestatico.')
            ->set('helpImageUrl', 'https://example.com/imagem.jpg')
            ->set('helpVideoUrl', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('save')
            ->assertRedirect(route('service-order-procedures-show'));

        $this->assertDatabaseHas('service_order_procedures', [
            'name' => 'Limpeza interna',
            'description' => 'Limpeza de poeira e conectores',
            'value' => '149.90',
            'help_text' => 'Use pincel antiestatico.',
            'help_image_url' => 'https://example.com/imagem.jpg',
            'help_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_can_update_procedure(): void
    {
        $procedure = ServiceOrderProcedure::factory()->create([
            'name' => 'Troca pasta termica',
            'value' => 89.90,
        ]);

        Livewire::test(ProcedureManagement::class, ['id' => $procedure->id])
            ->set('name', 'Troca de pasta termica premium')
            ->set('description', 'Inclui limpeza e aplicacao da pasta')
            ->set('value', '119.90')
            ->set('helpText', 'Aplicar camada fina e uniforme.')
            ->call('save')
            ->assertRedirect(route('service-order-procedures-show'));

        $this->assertDatabaseHas('service_order_procedures', [
            'id' => $procedure->id,
            'name' => 'Troca de pasta termica premium',
            'value' => '119.90',
            'help_text' => 'Aplicar camada fina e uniforme.',
        ]);
    }
}

