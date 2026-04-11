---
name: seeds-em-lote-performance
description: "Use quando criar ou refatorar seeders com muitos registros, priorizando insert em lote, integridade relacional e performance."
---

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
5. Manter integridade relacional (FKs e dependencias) mesmo com insercao em lote.
6. Cobrir funcionalidades do modulo/submodulo impactadas pelo seeder.
7. Validar com testes e `php artisan dev:reinstall`.

## Criterios de aceite
- Seeder de volume executa em lote.
- Menor numero de queries sem perda funcional.
- Relacionamentos preservados corretamente no lote.
- Funcionalidades do modulo/submodulo abastecidas com dados consistentes.
- Banco reconstruido e preenchido corretamente.

## Nao fazer
- Nao manter loop de escrita quando lote resolve.
- Nao quebrar relacionamento por montar lote sem dependencias necessarias.
- Nao usar `upsert` sem chave unica apropriada.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

