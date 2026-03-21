<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FinancialCardBrandFactory extends Factory
{
    protected $model = FinancialCardBrand::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => ucfirst($this->faker->unique()->word()),
            'is_active' => true,
        ];
    }
}
