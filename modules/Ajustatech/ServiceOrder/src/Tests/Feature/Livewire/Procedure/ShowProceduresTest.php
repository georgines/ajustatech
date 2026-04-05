<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_deletes_all_media_and_files_when_deleting_procedure(): void
    {
        Storage::fake('public');

        $procedure = ServiceOrderProcedure::factory()->create();
        $imagePath = 'service-order/procedures/images/procedure-image.jpg';
        $pdfPath = 'service-order/procedures/pdfs/procedure-manual.pdf';

        Storage::disk('public')->put($imagePath, 'image-content');
        Storage::disk('public')->put($pdfPath, 'pdf-content');

        $imageMedia = ServiceOrderProcedureMedia::factory()->create([
            'procedure_id' => $procedure->id,
            'type' => ServiceOrderProcedureMedia::TYPE_IMAGE,
            'disk' => 'public',
            'path' => $imagePath,
            'url' => null,
        ]);

        $pdfMedia = ServiceOrderProcedureMedia::factory()->create([
            'procedure_id' => $procedure->id,
            'type' => ServiceOrderProcedureMedia::TYPE_PDF,
            'disk' => 'public',
            'path' => $pdfPath,
            'url' => null,
        ]);

        $videoMedia = ServiceOrderProcedureMedia::factory()->video()->create([
            'procedure_id' => $procedure->id,
        ]);

        Livewire::test(ShowProcedures::class)
            ->call('deleteProcedure', $procedure->id);

        $this->assertDatabaseMissing('service_order_procedures', [
            'id' => $procedure->id,
        ]);
        $this->assertDatabaseMissing('service_order_procedure_media', [
            'id' => $imageMedia->id,
        ]);
        $this->assertDatabaseMissing('service_order_procedure_media', [
            'id' => $pdfMedia->id,
        ]);
        $this->assertDatabaseMissing('service_order_procedure_media', [
            'id' => $videoMedia->id,
        ]);
        Storage::disk('public')->assertMissing($imagePath);
        Storage::disk('public')->assertMissing($pdfPath);
    }
}
