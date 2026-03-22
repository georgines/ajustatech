<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Ajustatech\ServiceOrder\Services\ServiceOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_order_loads_active_fields_automatically_and_keeps_snapshot(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Maria',
            'equipment_name' => 'Notebook',
            'brand' => 'Dell',
            'model' => 'G15',
            'serial_number' => 'ABC123',
            'entry_date' => '2026-03-21',
            'reported_issue' => 'Nao liga',
        ]);

        $this->assertNotEmpty($order->fields_snapshot);
        $this->assertSame('Notebook', $order->equipment_type_snapshot['name']);
        $this->assertSame('observacoes_entrada', $order->fields_snapshot[1]['slug']);

        $equipmentTypeService->update($equipmentType->id, [
            ...$this->equipmentTypePayload(),
            'fields' => [
                ...$this->equipmentTypePayload()['fields'],
                [
                    'field_type' => 'text',
                    'name' => 'Novo campo',
                    'slug' => 'novo_campo',
                    'sort_order' => 10,
                    'is_required' => false,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [],
                ],
            ],
        ]);

        $order->refresh();
        $snapshotSlugs = collect($order->fields_snapshot)->pluck('slug')->all();
        $this->assertNotContains('novo_campo', $snapshotSlugs);
    }

    public function test_validates_required_fields_and_persists_values_and_attachments(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Joao',
            'equipment_name' => 'Notebook',
            'entry_date' => '2026-03-21',
        ]);

        $this->expectException(ValidationException::class);
        $serviceOrderService->fillFields($order, [
            'estado_carcaca' => 'bom',
            'acompanha_carregador' => 'sim',
        ], []);
    }

    public function test_persists_structured_values_and_attachments(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Joao',
            'equipment_name' => 'Notebook',
            'brand' => 'Lenovo',
            'model' => 'L340',
            'serial_number' => 'SER123',
            'entry_date' => '2026-03-21',
            'reported_issue' => 'Tela sem imagem',
        ]);

        $serviceOrderService->fillFields(
            $order,
            [
                'observacoes_entrada' => 'Carcaca com riscos',
                'estado_carcaca' => 'bom',
                'acompanha_carregador' => 'sim',
                'termo_entrada' => 'Cliente {{cliente_nome}} trouxe {{equipamento_nome}} em {{data_entrada}}',
            ],
            [
                'foto_frontal' => [
                    [
                        'path' => 'service-orders/foto-1.jpg',
                        'original_name' => 'foto-1.jpg',
                        'extension' => 'jpg',
                        'mime_type' => 'image/jpeg',
                        'size' => 1200,
                    ],
                ],
                'anexo_os' => [
                    [
                        'path' => 'service-orders/arquivo.pdf',
                        'original_name' => 'arquivo.pdf',
                        'extension' => 'pdf',
                        'mime_type' => 'application/pdf',
                        'size' => 6500,
                    ],
                ],
            ]
        );

        $this->assertDatabaseHas('service_order_field_values', [
            'service_order_id' => $order->id,
            'field_slug' => 'estado_carcaca',
            'value_text' => 'bom',
        ]);
        $this->assertDatabaseHas('service_order_attachments', [
            'service_order_id' => $order->id,
            'field_slug' => 'foto_frontal',
            'attachment_type' => 'photo',
        ]);
        $this->assertDatabaseHas('service_order_attachments', [
            'service_order_id' => $order->id,
            'field_slug' => 'anexo_os',
            'attachment_type' => 'file',
        ]);
    }

    public function test_build_print_payload_ignores_photo_and_file_and_renders_document(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Ana',
            'equipment_name' => 'Notebook',
            'brand' => 'HP',
            'model' => 'Pavilion',
            'serial_number' => 'HP123',
            'entry_date' => '2026-03-21',
            'reported_issue' => 'Nao carrega',
        ]);

        $serviceOrderService->fillFields(
            $order,
            [
                'observacoes_entrada' => 'Carcaça com marca de uso',
                'estado_carcaca' => 'bom',
                'acompanha_carregador' => 'sim',
                'termo_entrada' => 'Cliente {{cliente_nome}} trouxe {{equipamento_nome}}',
            ],
            [
                'foto_frontal' => [[
                    'path' => 'service-orders/foto.jpg',
                    'original_name' => 'foto.jpg',
                    'extension' => 'jpg',
                ]],
                'anexo_os' => [[
                    'path' => 'service-orders/arquivo.pdf',
                    'original_name' => 'arquivo.pdf',
                    'extension' => 'pdf',
                ]],
            ]
        );

        $payload = $serviceOrderService->buildPrintPayload($order->id);

        $slugs = collect($payload['fields'])->pluck('slug')->all();
        $this->assertContains('observacoes_entrada', $slugs);
        $this->assertContains('estado_carcaca', $slugs);
        $this->assertContains('acompanha_carregador', $slugs);
        $this->assertContains('termo_entrada', $slugs);
        $this->assertNotContains('foto_frontal', $slugs);
        $this->assertNotContains('anexo_os', $slugs);

        $documentField = collect($payload['fields'])->firstWhere('slug', 'termo_entrada');
        $this->assertStringContainsString('Cliente Ana trouxe Notebook', $documentField['value']);
    }

    public function test_rejects_attachment_with_invalid_extension(): void
    {
        $this->expectException(ValidationException::class);

        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Ana',
            'equipment_name' => 'Notebook',
            'entry_date' => '2026-03-21',
        ]);

        $serviceOrderService->fillFields(
            $order,
            [
                'observacoes_entrada' => 'Ok',
                'estado_carcaca' => 'bom',
                'acompanha_carregador' => 'sim',
                'termo_entrada' => 'Termo {{cliente_nome}}',
            ],
            [
                'anexo_os' => [[
                    'path' => 'service-orders/arquivo.exe',
                    'original_name' => 'arquivo.exe',
                    'extension' => 'exe',
                ]],
            ]
        );
    }

    public function test_can_create_order_from_existing_customer_id(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());
        $customer = Customer::factory()->create(['name' => 'Cliente Existente']);

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_id' => $customer->id,
            'equipment_name' => 'Notebook',
            'entry_date' => '2026-03-21',
        ]);

        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('Cliente Existente', $order->customer_name);
    }

    public function test_can_attach_registered_services_to_order(): void
    {
        $equipmentTypeService = app(EquipmentTypeService::class);
        $serviceOrderService = app(ServiceOrderService::class);
        $equipmentType = $equipmentTypeService->create($this->equipmentTypePayload());

        $order = $serviceOrderService->create([
            'equipment_type_id' => $equipmentType->id,
            'customer_name' => 'Joana',
            'equipment_name' => 'Notebook',
            'entry_date' => '2026-03-21',
        ]);

        $serviceA = ServiceCatalogService::factory()->create(['name' => 'Formatacao', 'base_price' => 150, 'is_active' => true]);
        $serviceB = ServiceCatalogService::factory()->create(['name' => 'Limpeza', 'base_price' => 90, 'is_active' => true]);
        $serviceA->steps()->createMany([
            [
                'name' => 'Checklist inicial',
                'sort_order' => 1,
                'is_required' => true,
                'help_text' => 'Verifique estado geral',
                'technician_report_label' => 'Relato do checklist',
                'requires_image_proof' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Executar formatacao',
                'sort_order' => 2,
                'is_required' => true,
                'help_text' => 'Instalar sistema e drivers',
                'technician_report_label' => 'Relato da formatacao',
                'requires_image_proof' => false,
                'is_active' => true,
            ],
        ]);

        $serviceOrderService->syncServices($order->id, [
            ['service_catalog_service_id' => $serviceA->id, 'quantity' => 1, 'discount' => 10.5],
            ['service_catalog_service_id' => $serviceB->id, 'quantity' => 2],
        ]);

        $this->assertDatabaseHas('service_order_service_items', [
            'service_order_id' => $order->id,
            'service_catalog_service_id' => $serviceA->id,
            'service_name' => 'Formatacao',
            'quantity' => 1,
            'discount_amount' => 10.50,
        ]);
        $this->assertDatabaseHas('service_order_service_items', [
            'service_order_id' => $order->id,
            'service_catalog_service_id' => $serviceB->id,
            'service_name' => 'Limpeza',
            'quantity' => 2,
        ]);

        $itemA = $order->fresh('serviceItems')->serviceItems->firstWhere('service_catalog_service_id', $serviceA->id);
        $this->assertNotNull($itemA);
        $this->assertSame('10.50', (string) $itemA->discount_amount);
        $steps = $itemA->service_snapshot['steps'] ?? [];
        $this->assertCount(2, $steps);
        $this->assertSame('Checklist inicial', $steps[0]['name']);
        $this->assertTrue($steps[0]['is_required']);
        $this->assertTrue($steps[0]['requires_image_proof']);
    }

    private function equipmentTypePayload(): array
    {
        return [
            'name' => 'Notebook',
            'description' => 'Template para notebook',
            'is_active' => true,
            'fields' => [
                [
                    'field_type' => 'photo',
                    'name' => 'Foto frontal',
                    'slug' => 'foto_frontal',
                    'sort_order' => 1,
                    'is_required' => true,
                    'is_printable' => false,
                    'is_active' => true,
                    'configuration' => [
                        'max_files' => 2,
                        'allowed_extensions' => ['jpg', 'png'],
                    ],
                ],
                [
                    'field_type' => 'text',
                    'name' => 'Observacoes de entrada',
                    'slug' => 'observacoes_entrada',
                    'sort_order' => 2,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [
                        'max_length' => 1000,
                    ],
                ],
                [
                    'field_type' => 'select',
                    'name' => 'Estado da carcaca',
                    'slug' => 'estado_carcaca',
                    'sort_order' => 3,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [],
                    'options' => [
                        ['label' => 'Bom', 'value' => 'bom', 'sort_order' => 1, 'is_active' => true],
                        ['label' => 'Regular', 'value' => 'regular', 'sort_order' => 2, 'is_active' => true],
                    ],
                ],
                [
                    'field_type' => 'radio',
                    'name' => 'Acompanha carregador',
                    'slug' => 'acompanha_carregador',
                    'sort_order' => 4,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [],
                    'options' => [
                        ['label' => 'Sim', 'value' => 'sim', 'sort_order' => 1, 'is_active' => true],
                        ['label' => 'Nao', 'value' => 'nao', 'sort_order' => 2, 'is_active' => true],
                    ],
                ],
                [
                    'field_type' => 'file',
                    'name' => 'Anexo OS',
                    'slug' => 'anexo_os',
                    'sort_order' => 5,
                    'is_required' => false,
                    'is_printable' => false,
                    'is_active' => true,
                    'configuration' => [
                        'allowed_extensions' => ['pdf', 'docx'],
                        'preview_mode' => 'new_tab',
                    ],
                ],
                [
                    'field_type' => 'document',
                    'name' => 'Termo de entrada',
                    'slug' => 'termo_entrada',
                    'sort_order' => 6,
                    'is_required' => true,
                    'is_printable' => true,
                    'is_active' => true,
                    'configuration' => [
                        'template' => 'Cliente {{cliente_nome}} trouxe {{equipamento_nome}}',
                    ],
                ],
            ],
        ];
    }
}
