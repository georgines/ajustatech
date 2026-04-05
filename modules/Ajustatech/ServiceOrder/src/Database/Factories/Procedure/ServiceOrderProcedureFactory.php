<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderProcedureFactory extends Factory
{
    protected $model = ServiceOrderProcedure::class;

    public function definition(): array
    {
        $catalog = [
            [
                'name' => 'Diagnostico tecnico completo',
                'description' => 'Analise geral de hardware e software para identificar causa da falha.',
                'help_text' => 'Registrar sintomas, testes executados e conclusao tecnica.',
                'value_min' => 79.90,
                'value_max' => 109.90,
            ],
            [
                'name' => 'Limpeza interna e externa',
                'description' => 'Remocao de poeira e higienizacao dos componentes.',
                'help_text' => 'Utilizar pincel antiestatico e ar comprimido em baixa pressao.',
                'value_min' => 99.90,
                'value_max' => 149.90,
            ],
            [
                'name' => 'Troca de pasta termica',
                'description' => 'Substituicao de pasta termica em processador.',
                'help_text' => 'Aplicar camada fina e uniforme para melhorar dissipacao.',
                'value_min' => 79.90,
                'value_max' => 129.90,
            ],
            [
                'name' => 'Formatacao e instalacao do sistema',
                'description' => 'Backup, formatacao e reinstalacao limpa do sistema operacional.',
                'help_text' => 'Confirmar programas essenciais e restaurar dados do cliente.',
                'value_min' => 149.90,
                'value_max' => 229.90,
            ],
            [
                'name' => 'Instalacao de SSD com migracao',
                'description' => 'Upgrade para SSD com clonagem ou migracao de dados.',
                'help_text' => 'Conferir compatibilidade SATA/NVMe e espaco necessario.',
                'value_min' => 199.90,
                'value_max' => 299.90,
            ],
            [
                'name' => 'Remocao de virus e malware',
                'description' => 'Varredura completa e limpeza de ameacas do sistema.',
                'help_text' => 'Atualizar antivirus e orientar praticas de seguranca.',
                'value_min' => 119.90,
                'value_max' => 189.90,
            ],
        ];

        $template = $this->faker->randomElement($catalog);
        $helpImageUrl = $this->faker->boolean(50) ? $this->faker->randomElement([
            'https://images.unsplash.com/photo-1581092160607-ee22731e0a08',
            'https://images.unsplash.com/photo-1591488320449-011701bb6704',
            'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f',
        ]) : null;
        $helpVideoUrl = $this->faker->boolean(40) ? $this->faker->randomElement([
            'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
            'https://www.youtube.com/watch?v=5d6Z6j8Vb5M',
            'https://www.youtube.com/watch?v=2lmfF0k2UcU',
        ]) : null;

        return [
            'id' => $this->faker->uuid(),
            'name' => $template['name'],
            'description' => $template['description'],
            'value' => $this->faker->randomFloat(2, $template['value_min'], $template['value_max']),
            'help_text' => $template['help_text'],
            'help_image_url' => $helpImageUrl,
            'help_video_url' => $helpVideoUrl,
        ];
    }
}
