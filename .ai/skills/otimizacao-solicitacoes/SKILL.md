---
name: otimizacao-solicitacoes
description: "Use quando otimizar requests HTTP/Livewire e queries de banco em fluxos de listar, salvar, editar e excluir."
---

# Skill: Otimizacao de Solicitacoes

## Procedimento
1. Mapear requests por fluxo.
2. Evitar request para acao puramente visual.
3. Garantir consultas com relacionamento sem N+1.
4. Eliminar query em loop.
5. Aplicar lote para insert/update/delete quando houver volume.

## Fonte
- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/filtros-tabelas-modulo.md`
