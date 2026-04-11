<?php

namespace Ajustatech\ServiceOrder\Database\Factories\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        $defaultStatusId = ServiceOrderStatusFlow::defaultInitialId();

        return [
            'id' => $this->faker->uuid(),
            'status_flow_id' => $defaultStatusId,
        ];
    }
}
