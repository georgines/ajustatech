<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Seeder;

class ServiceOrderProcedureSeeder extends Seeder
{
    public function run(): void
    {
        $procedures = [
            [
                'name' => 'Diagnostico tecnico completo',
                'description' => 'Analise geral de hardware e software para identificar causa da falha.',
                'value' => 89.90,
                'help_text' => 'Registrar sintomas, testes executados e conclusao tecnica.',
                'help_image_url' => 'https://images.unsplash.com/photo-1580894732444-8ecded7900cd',
                'help_video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
            ],
            [
                'name' => 'Limpeza interna e externa',
                'description' => 'Remocao de poeira, higienizacao de gabinete e conectores.',
                'value' => 119.90,
                'help_text' => 'Utilizar pincel antiestatico e ar comprimido em baixa pressao.',
                'help_image_url' => 'https://images.unsplash.com/photo-1581092160607-ee22731e0a08',
                'help_video_url' => null,
            ],
            [
                'name' => 'Troca de pasta termica',
                'description' => 'Substituicao da pasta termica em CPU e GPU quando aplicavel.',
                'value' => 99.90,
                'help_text' => 'Aplicar camada fina e uniforme no centro do processador.',
                'help_image_url' => null,
                'help_video_url' => 'https://www.youtube.com/watch?v=5d6Z6j8Vb5M',
            ],
            [
                'name' => 'Formatacao e instalacao do sistema',
                'description' => 'Backup, formatacao e instalacao limpa do sistema operacional.',
                'value' => 179.90,
                'help_text' => 'Confirmar com o cliente a versao desejada e os dados a preservar.',
                'help_image_url' => null,
                'help_video_url' => null,
            ],
            [
                'name' => 'Instalacao de SSD com migracao',
                'description' => 'Substituicao para SSD com clonagem ou migracao de dados.',
                'value' => 249.90,
                'help_text' => 'Verificar compatibilidade SATA/NVMe antes da instalacao.',
                'help_image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704',
                'help_video_url' => null,
            ],
            [
                'name' => 'Troca de tela notebook',
                'description' => 'Substituicao do display com teste de brilho e pixels.',
                'value' => 329.90,
                'help_text' => 'Conferir modelo, conector e taxa de atualizacao do painel.',
                'help_image_url' => 'https://images.unsplash.com/photo-1517336714739-489689fd1ca8',
                'help_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'name' => 'Troca de teclado notebook',
                'description' => 'Substituicao de teclado com limpeza de base e validacao de teclas.',
                'value' => 219.90,
                'help_text' => 'Testar todas as teclas e atalhos apos montagem.',
                'help_image_url' => null,
                'help_video_url' => null,
            ],
            [
                'name' => 'Atualizacao de BIOS/UEFI',
                'description' => 'Atualizacao controlada de firmware para estabilidade e compatibilidade.',
                'value' => 149.90,
                'help_text' => 'Garantir energia estavel e arquivo correto da placa mae.',
                'help_image_url' => null,
                'help_video_url' => 'https://www.youtube.com/watch?v=2lmfF0k2UcU',
            ],
            [
                'name' => 'Remocao de virus e malware',
                'description' => 'Varredura, remocao de ameacas e reforco basico de seguranca.',
                'value' => 139.90,
                'help_text' => 'Instalar protecao basica e orientar boas praticas ao cliente.',
                'help_image_url' => 'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f',
                'help_video_url' => null,
            ],
            [
                'name' => 'Recuperacao de dados',
                'description' => 'Tentativa de recuperacao logica de arquivos em midia com falha.',
                'value' => 399.90,
                'help_text' => 'Documentar arquivos recuperados e limitacoes do processo.',
                'help_image_url' => null,
                'help_video_url' => null,
            ],
        ];

        foreach ($procedures as $procedure) {
            ServiceOrderProcedure::query()->updateOrCreate(
                ['name' => $procedure['name']],
                $procedure
            );
        }
    }
}
