<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipmentTypeFactory extends Factory
{
    protected $model = EquipmentType::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->randomElement(['Notebook', 'Desktop', 'Celular', 'Tablet']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'image_disk' => null,
            'image_path' => null,
            'image_original_name' => null,
            'image_mime_type' => null,
            'image_size' => null,
        ];
    }

    public function withStoredImage(string $disk = 'public'): self
    {
        return $this->state(fn () => [
            'image_disk' => $disk,
            'image_path' => 'equipment-types/demo/example-image.jpg',
            'image_original_name' => 'example-image.jpg',
            'image_mime_type' => 'image/jpeg',
            'image_size' => 125000,
        ]);
    }
}
