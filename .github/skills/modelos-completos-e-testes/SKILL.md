---
name: modelos-completos-e-testes
description: "Use quando criar ou refatorar models, garantindo responsabilidade correta, performance e cobertura de testes."
---

# Skill: Modelos Completos e Testes

## Procedimento

1. Manter logica de dominio no Model.
2. Service apenas orquestra fluxo.
3. Evitar N+1 e query em loop.
4. Em volume, usar lote para insert/update/delete.
5. Garantir migration, factory, seeder e testes da feature.

## Fonte

- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/testes-feature-tdd.md`