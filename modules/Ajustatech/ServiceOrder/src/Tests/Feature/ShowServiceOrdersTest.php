<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowServiceOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_translates_order_status_to_portuguese(): void
    {
        ServiceOrder::factory()->open()->create();
        ServiceOrder::factory()->cancelled()->create();
        ServiceOrder::factory()->completed()->create();

        Livewire::test(ShowServiceOrders::class)
            ->assertSee('Aberta')
            ->assertSee('Cancelada')
            ->assertSee('Concluida');
    }

    public function test_can_cancel_order_from_listing(): void
    {
        $order = ServiceOrder::factory()->open()->create();

        Livewire::test(ShowServiceOrders::class)
            ->call('cancelOrder', $order->id);

        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'status' => 'canceled',
        ]);
    }

    public function test_documents_screen_shows_document_fields_from_order_snapshot(): void
    {
        $order = ServiceOrder::factory()->open()->create([
            'fields_snapshot' => [
                [
                    'id' => 'field-1',
                    'name' => 'Termo de entrada',
                    'slug' => 'termo_entrada',
                    'field_type' => 'document',
                    'sort_order' => 1,
                    'is_printable' => true,
                ],
                [
                    'id' => 'field-2',
                    'name' => 'Observacoes',
                    'slug' => 'observacoes',
                    'field_type' => 'text',
                    'sort_order' => 2,
                    'is_printable' => true,
                ],
            ],
        ]);

        $order->fieldValues()->create([
            'field_slug' => 'termo_entrada',
            'field_type' => 'document',
            'value_text' => 'Documento preenchido',
            'field_snapshot' => [
                'name' => 'Termo de entrada',
                'slug' => 'termo_entrada',
                'field_type' => 'document',
            ],
        ]);

        $this->get(route('service-order-orders-documents', ['id' => $order->id]))
            ->assertOk()
            ->assertSee('Termo de entrada')
            ->assertSee('Documento preenchido');
    }

    public function test_documents_action_is_disabled_when_order_has_no_document_field(): void
    {
        $order = ServiceOrder::factory()->open()->create([
            'fields_snapshot' => [
                [
                    'id' => 'field-2',
                    'name' => 'Observacoes',
                    'slug' => 'observacoes',
                    'field_type' => 'text',
                    'sort_order' => 1,
                    'is_printable' => true,
                ],
            ],
        ]);

        $this->get(route('service-order-orders-show'))
            ->assertOk()
            ->assertSee('pe-none opacity-50', false)
            ->assertSee(route('service-order-orders-documents', ['id' => $order->id]), false);
    }
}
