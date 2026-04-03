<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'order_number' => null,
            'equipment_type_id' => EquipmentType::factory(),
            'customer_id' => null,
            'customer_name' => $this->faker->name(),
            'equipment_name' => $this->faker->randomElement(['Notebook', 'Celular', 'Desktop']),
            'brand' => $this->faker->company(),
            'model' => strtoupper($this->faker->bothify('??-###')),
            'serial_number' => strtoupper($this->faker->bothify('SN-########')),
            'entry_date' => Carbon::now()->toDateString(),
            'reported_issue' => $this->faker->sentence(),
            'status' => 'open',
            'equipment_type_snapshot' => ['id' => null, 'name' => 'Snapshot', 'description' => null],
            'fields_snapshot' => [],
        ];
    }

    public function open(): self
    {
        return $this->state(fn () => ['status' => 'open']);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => ['status' => 'canceled']);
    }

    public function completed(): self
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
