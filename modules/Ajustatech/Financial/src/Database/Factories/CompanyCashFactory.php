<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyCashFactory extends Factory
{
    protected $model = CompanyCash::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->word());

        return [
            'id' => (string) Str::uuid(),
            'user_id' => (string) Str::uuid(),
            'user_name' => $this->faker->name(),
            'cash_name' => "Caixa {$name}",
            'description' => $this->faker->optional()->sentence(),
            'agency' => $this->faker->optional()->numerify('####'),
            'account' => $this->faker->optional()->numerify('#####-#'),
            'is_online' => $this->faker->boolean(),
            'is_active' => true,
            'is_managerial' => false,
        ];
    }

    public function managerial(): static
    {
        return $this->state(fn () => ['is_managerial' => true]);
    }

    public function nonManagerial(): static
    {
        return $this->state(fn () => ['is_managerial' => false]);
    }
}
