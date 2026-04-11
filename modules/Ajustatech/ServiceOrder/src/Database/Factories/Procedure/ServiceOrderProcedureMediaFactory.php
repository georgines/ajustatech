<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderProcedureMediaFactory extends Factory
{
    protected $model = ServiceOrderProcedureMedia::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'procedure_id' => ServiceOrderProcedure::factory(),
            'type' => $this->faker->randomElement([
                ServiceOrderProcedureMedia::TYPE_IMAGE,
                ServiceOrderProcedureMedia::TYPE_VIDEO,
                ServiceOrderProcedureMedia::TYPE_PDF,
            ]),
            'url' => null,
            'disk' => null,
            'path' => null,
            'original_name' => null,
            'description' => $this->faker->optional()->sentence(8),
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function video(): self
    {
        return $this->state(fn () => [
            'type' => ServiceOrderProcedureMedia::TYPE_VIDEO,
            'url' => $this->faker->randomElement([
                'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'https://www.youtube.com/watch?v=2lmfF0k2UcU',
            ]),
            'disk' => null,
            'path' => null,
            'original_name' => null,
        ]);
    }
}

