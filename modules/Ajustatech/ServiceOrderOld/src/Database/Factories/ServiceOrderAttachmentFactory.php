<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderAttachment;
use Ajustatech\ServiceOrderOld\Support\EquipmentFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderAttachmentFactory extends Factory
{
    protected $model = ServiceOrderAttachment::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_order_id' => ServiceOrder::factory(),
            'equipment_type_field_id' => EquipmentTypeField::factory(),
            'field_slug' => $this->faker->slug(2, '_'),
            'attachment_type' => $this->faker->randomElement([EquipmentFieldType::PHOTO, EquipmentFieldType::FILE]),
            'disk' => 'public',
            'path' => 'service-orders/' . $this->faker->uuid() . '.jpg',
            'original_name' => $this->faker->word() . '.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => $this->faker->numberBetween(2000, 3000000),
            'metadata' => ['preview_url' => 'https://example.test/preview'],
        ];
    }
}
