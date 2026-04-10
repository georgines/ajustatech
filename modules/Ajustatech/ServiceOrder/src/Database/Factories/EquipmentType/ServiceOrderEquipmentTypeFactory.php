<?php

namespace Ajustatech\ServiceOrder\Database\Factories\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentTypeFactory extends Factory
{
    protected $model = ServiceOrderEquipmentType::class;

    public function definition(): array
    {
        $catalog = [
            [
                'name' => 'Computador desktop',
                'description' => 'Equipamentos de bancada para uso residencial e corporativo.',
            ],
            [
                'name' => 'Notebook',
                'description' => 'Equipamentos portateis para trabalho e estudo.',
            ],
            [
                'name' => 'Celular',
                'description' => 'Smartphones Android e iOS para reparo e manutencao.',
            ],
            [
                'name' => 'Tablet',
                'description' => 'Dispositivos moveis com tela touch para produtividade e consumo de conteudo.',
            ],
            [
                'name' => 'Impressora',
                'description' => 'Impressoras jato de tinta e laser para assistencia tecnica.',
            ],
        ];

        $template = $this->faker->randomElement($catalog);

        return [
            'id' => $this->faker->uuid(),
            'name' => $template['name'],
            'description' => $template['description'],
            'is_active' => $this->faker->boolean(85),
        ];
    }
}
