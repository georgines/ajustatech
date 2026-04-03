<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Seeders\EquipmentTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentTypesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_types_seeder_is_idempotent_and_creates_expected_variations(): void
    {
        $this->seed(EquipmentTypesSeeder::class);
        $this->seed(EquipmentTypesSeeder::class);

        $this->assertSame(3, EquipmentType::query()->count());

        $notebook = EquipmentType::query()->where('name', 'Notebook')->first();
        $desktop = EquipmentType::query()->where('name', 'Desktop')->first();
        $celular = EquipmentType::query()->where('name', 'Celular')->first();

        $this->assertNotNull($notebook);
        $this->assertNotNull($desktop);
        $this->assertNotNull($celular);

        $this->assertNotNull($notebook->fields()->where('field_type', 'photo')->first());
        $this->assertNotNull($notebook->fields()->where('field_type', 'text')->first());
        $this->assertNotNull($notebook->fields()->where('field_type', 'select')->first());
        $this->assertNotNull($notebook->fields()->where('field_type', 'radio')->first());
        $this->assertNotNull($notebook->fields()->where('field_type', 'document')->first());
        $this->assertNotNull($desktop->fields()->where('field_type', 'file')->first());
    }
}
