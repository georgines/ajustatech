<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EquipmentTypeFieldFactory extends Factory
{
    protected $model = EquipmentTypeField::class;

    public function definition(): array
    {
        $fieldType = Arr::random(EquipmentFieldType::values());

        return [
            'id' => (string) Str::uuid(),
            'equipment_type_id' => EquipmentType::factory(),
            'field_type' => $fieldType,
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug('_'),
            'sort_order' => $this->faker->numberBetween(1, 20),
            'is_required' => $this->faker->boolean(60),
            'is_printable' => in_array($fieldType, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true)
                ? false
                : $this->faker->boolean(70),
            'is_active' => true,
            'configuration' => $this->defaultConfiguration($fieldType),
        ];
    }

    private function defaultConfiguration(string $fieldType): array
    {
        return match ($fieldType) {
            EquipmentFieldType::PHOTO => [
                'max_files' => 3,
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            ],
            EquipmentFieldType::FILE => [
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'preview_mode' => 'modal',
            ],
            EquipmentFieldType::TEXT => [
                'placeholder' => 'Digite aqui',
                'help' => 'Informacao complementar',
                'max_length' => 500,
            ],
            EquipmentFieldType::DOCUMENT => [
                'template' => 'Cliente: {{cliente_nome}}',
                'help' => 'Aceita variaveis do catalogo.',
            ],
            default => [],
        };
    }
}

