<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Livewire\ServiceCatalogManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_add_new_step_when_current_step_name_is_empty(): void
    {
        Livewire::test(ServiceCatalogManagement::class)
            ->assertCount('steps', 1)
            ->call('addStep')
            ->assertHasErrors(['steps'])
            ->assertCount('steps', 1);
    }

    public function test_add_step_keeps_form_and_existing_step_state(): void
    {
        Livewire::test(ServiceCatalogManagement::class)
            ->set('name', 'Analise de notebook')
            ->set('base_price', 90)
            ->set('description', 'Analise inicial')
            ->set('steps.0.name', 'Teste de ligar')
            ->set('steps.0.help_text', 'Verificar imagem na tela')
            ->call('addStep')
            ->assertHasNoErrors()
            ->assertCount('steps', 2)
            ->assertSet('name', 'Analise de notebook')
            ->assertSet('base_price', 90)
            ->assertSet('description', 'Analise inicial')
            ->assertSet('steps.0.name', 'Teste de ligar')
            ->assertSet('steps.0.help_text', 'Verificar imagem na tela')
            ->assertSet('steps.1.sort_order', 2);
    }

    public function test_move_step_reindexes_without_corrupting_steps(): void
    {
        Livewire::test(ServiceCatalogManagement::class)
            ->set('steps.0.name', 'Procedimento A')
            ->call('addStep')
            ->set('steps.1.name', 'Procedimento B')
            ->call('moveStepUp', 1)
            ->assertSet('steps.0.name', 'Procedimento B')
            ->assertSet('steps.0.sort_order', 1)
            ->assertSet('steps.1.name', 'Procedimento A')
            ->assertSet('steps.1.sort_order', 2)
            ->call('moveStepDown', 0)
            ->assertSet('steps.0.name', 'Procedimento A')
            ->assertSet('steps.0.sort_order', 1)
            ->assertSet('steps.1.name', 'Procedimento B')
            ->assertSet('steps.1.sort_order', 2);
    }

    public function test_can_save_service_with_ordered_steps(): void
    {
        Livewire::test(ServiceCatalogManagement::class)
            ->set('name', 'Analise de notebook')
            ->set('description', 'Analise inicial completa')
            ->set('base_price', 90)
            ->set('is_active', true)
            ->set('is_reusable', true)
            ->set('steps.0.name', 'Teste de ligar')
            ->set('steps.0.help_text', 'Verificar imagem na tela')
            ->set('steps.0.technician_report_label', 'Relato do teste de ligar')
            ->set('steps.0.is_required', true)
            ->set('steps.0.requires_image_proof', true)
            ->call('addStep')
            ->set('steps.1.name', 'Inspecao visual')
            ->set('steps.1.technician_report_label', 'Relato da inspecao')
            ->call('save')
            ->assertHasNoErrors();

        $service = ServiceCatalogService::query()->where('name', 'Analise de notebook')->first();
        $this->assertNotNull($service);

        $this->assertDatabaseHas('service_catalog_services', [
            'id' => $service->id,
            'name' => 'Analise de notebook',
            'is_active' => true,
            'is_reusable' => true,
        ]);

        $this->assertDatabaseHas('service_catalog_service_steps', [
            'service_catalog_service_id' => $service->id,
            'name' => 'Teste de ligar',
            'sort_order' => 1,
            'is_required' => true,
            'requires_image_proof' => true,
        ]);

        $this->assertDatabaseHas('service_catalog_service_steps', [
            'service_catalog_service_id' => $service->id,
            'name' => 'Inspecao visual',
            'sort_order' => 2,
        ]);
    }
}
