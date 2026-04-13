<?php

namespace Ajustatech\Settings\Database\Factories\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderSettingFactory extends Factory
{
    protected $model = ServiceOrderSetting::class;

    public function definition(): array
    {
        return [
            'initial_order_number' => $this->faker->numberBetween(1000, 9999),
        ];
    }
}
