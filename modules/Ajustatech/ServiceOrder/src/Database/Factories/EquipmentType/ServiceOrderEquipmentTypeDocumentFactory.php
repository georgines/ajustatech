<?php

namespace Ajustatech\ServiceOrder\Database\Factories\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentTypeDocumentFactory extends Factory
{
    protected $model = ServiceOrderEquipmentTypeDocument::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'equipment_type_id' => ServiceOrderEquipmentType::factory(),
            'document_type' => $this->faker->randomElement([
                ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
                ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
            ]),
            'title' => $this->faker->randomElement([
                'Contrato de servico',
                'Recibo de entrega',
                'Autorizacao de manutencao',
            ]),
            'description' => $this->faker->optional()->sentence(8),
            'template_content' => null,
            'variables_json' => null,
            'disk' => null,
            'path' => null,
            'original_name' => null,
            'mime_type' => null,
            'extension' => null,
            'size' => null,
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function editableTemplate(): self
    {
        return $this->state(fn () => [
            'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
            'template_content' => 'Contrato para {{dados_cliente}} referente ao equipamento {{equipamento_modelo}}.',
            'variables_json' => ['dados_cliente', 'equipamento_modelo', 'numero_ordem_servico'],
        ]);
    }
}
