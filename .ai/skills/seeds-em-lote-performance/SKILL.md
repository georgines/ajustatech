# Skill: Seeds em Lote (Performance)

## Objetivo
Aplicar insercao em lote em seeders para reduzir solicitacoes ao banco.

## Quando usar
- Ao criar seeders novos com multiplos registros.
- Ao refatorar seeders que usam loop com `create`/`updateOrCreate`.

## Fonte de verdade
- `/.ai/guidelines/projeto/seeds-em-lote-performance.md`

## Procedimento obrigatorio
1. Identificar seeder com insercoes repetidas em loop.
2. Montar array de registros finais com `id` e timestamps.
3. Substituir por `Model::query()->insert($rows)`.
4. Manter dados reais e coerentes com o dominio.
5. Validar com testes e `php artisan dev:reinstall`.

## Criterios de aceite
- Seeder de volume executa em lote.
- Menor numero de queries sem perda funcional.
- Banco reconstruido e preenchido corretamente.

## Nao fazer
- Nao manter loop de escrita quando lote resolve.
- Nao usar `upsert` sem chave unica apropriada.
