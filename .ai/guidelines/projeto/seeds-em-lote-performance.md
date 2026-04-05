# Seeds em Lote (Performance)

## Objetivo
Padronizar seeders para inserir muitos registros com o menor numero de queries possivel.

## Regra obrigatoria
- Quando um seeder inserir varios registros, preferir insercao em lote (`insert`) em vez de loop com `create`/`updateOrCreate`.
- A meta padrao e 1 query de escrita por conjunto de registros do mesmo recurso.

## Padrao recomendado
1. Montar array final com todos os registros.
2. Adicionar `id`, `created_at` e `updated_at` no proprio array.
3. Executar `Model::query()->insert($rows)`.

## Quando usar `upsert`
- Usar `upsert` somente quando houver necessidade real de atualizar registros existentes.
- Se usar `upsert`, garantir chave unica/indice adequado para evitar comportamento inesperado.

## Anti-padroes
- Loop com `create()` para dezenas/centenas de registros.
- Loop com `updateOrCreate()` quando ambiente e reconstruido por `dev:reinstall`.
- Multiplicar queries sem necessidade em seeders de carga base.

## Checklist de conclusao
1. Seeder de volume esta em lote?
2. IDs e timestamps foram definidos corretamente?
3. Dados continuam reais/coerentes com a funcionalidade?
4. Seeder funciona apos `php artisan dev:reinstall`?
