<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->set('hasHelp', true)
            ->set('helpText', 'Use pincel antiestatico.')
            ->set('videoItems.0.url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->set('videoItems.0.name', 'Video de limpeza')
            ->set('videoItems.0.description', 'Video de apoio.')
            ->set('imageItems.0.file', UploadedFile::fake()->image('procedimento.jpg'))
            ->set('imageItems.0.name', 'Imagem interna')
            ->set('imageItems.0.description', 'Imagem de apoio.')
            ->set('pdfItems.0.file', UploadedFile::fake()->create('manual.pdf', 200, 'application/pdf'))
            ->set('pdfItems.0.name', 'Manual de limpeza')
            ->set('pdfItems.0.description', 'PDF com orientacoes.')
            ->call('save')
            ->assertRedirect(route('service-order-procedures-show'));

        $this->assertDatabaseHas('service_order_procedures', [
            'name' => 'Limpeza interna',
            'description' => 'Limpeza de poeira e conectores',
            'value' => '149.90',
            'help_text' => 'Use pincel antiestatico.',
        ]);

        $this->assertDatabaseHas('service_order_procedure_media', [
            'type' => 'video',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => 'Video de apoio.',
        ]);

        $this->assertDatabaseHas('service_order_procedure_media', [
            'type' => 'image',
            'description' => 'Imagem de apoio.',
        ]);

        $this->assertDatabaseHas('service_order_procedure_media', [
            'type' => 'pdf',
            'description' => 'PDF com orientacoes.',
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
            ->set('hasHelp', true)
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

    public function test_does_not_persist_help_when_switch_is_disabled(): void
    {
        Livewire::test(ProcedureManagement::class)
            ->set('name', 'Atualizacao de driver')
            ->set('description', 'Atualizacao de drivers essenciais')
            ->set('value', '79.90')
            ->set('hasHelp', false)
            ->set('helpText', 'Texto que nao deve ser salvo')
            ->set('videoItems.0.url', 'https://example.com/help.mp4')
            ->call('save')
            ->assertRedirect(route('service-order-procedures-show'));

        $this->assertDatabaseHas('service_order_procedures', [
            'name' => 'Atualizacao de driver',
            'help_text' => null,
            'help_image_url' => null,
            'help_video_url' => null,
        ]);
    }
}
