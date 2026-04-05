# Pos-Testes Obrigatorio: `dev:reinstall`

## Objetivo
Garantir que, apos a execucao dos testes, o ambiente de desenvolvimento volte para um estado consistente com banco reconstruido e dados preenchidos.

## Regra obrigatoria
Ao finalizar qualquer implementacao com execucao de testes, sempre rodar:

```bash
php artisan dev:reinstall
```

## O que o comando deve garantir
- limpeza do banco atual;
- execucao de migrations;
- limpeza de caches de desenvolvimento;
- execucao de seeds para deixar o sistema preenchido.

## Quando aplicar
- Depois de `php artisan test` parcial ou completo.
- Depois de alterar migrations, models, factories, seeders ou fluxos dependentes de dados.
- Antes de considerar a tarefa concluida para entrega local.

## Checklist de conclusao
1. Testes executados e resultado registrado.
2. `php artisan dev:reinstall` executado com sucesso.
3. Banco reconstruido e dados seedados disponiveis para validacao manual.
4. Sem erro pendente de migration/seeder apos reinstall.
