# Seeds Obrigatorios para Recursos e Modulos

## Regra principal
Ao criar um recurso novo ou um modulo novo, sempre criar seeds com o maximo de cenarios possiveis para povoar o banco.

## Objetivo
- Garantir base de dados rica para desenvolvimento e testes.
- Facilitar validacao de fluxos reais da aplicacao.
- Reduzir dados manuais e ambientes vazios.

## O que deve ser seedado
- Variacoes principais do dominio (status, tipos, categorias, perfis, etc.).
- Casos comuns de uso.
- Casos de borda relevantes para o recurso.
- Relacionamentos entre entidades, quando existirem.

## Regras por modulo
- Cada modulo deve ter seus seeders em `Database/Seeders`.
- Cada modulo deve expor comando `module:seed-*`.
- O comando global `module:seed` deve conseguir executar todos os seeds de modulos.

## Execucao obrigatoria apos criar/alterar recurso
Sempre rodar:
1. `php artisan dev:migrate`
2. `php artisan dev:seed`

Ou, quando necessario:
1. `php artisan module:seed`

## Criterio de pronto
Uma feature/modulo so e considerada pronta quando:
- seeds cobrindo cenarios relevantes foram criados/atualizados;
- o povoamento global dos modulos foi executado com sucesso.

