---
name: gate-codigo-novo
description: "Use quando criar ou alterar codigo relevante e precisar validar arquitetura, seguranca, performance, testes e restauracao de ambiente."
---

# Skill: Gate de Codigo Novo

## Quando usar
- Qualquer codigo novo.
- Mudanca de regra de negocio, persistencia, validacao, Livewire, seeds ou servicos.

## Procedimento
1. Validar arquitetura e camada correta.
2. Validar sanitizacao + validacao backend.
3. Garantir mensagens de validacao em pt-BR.
4. Revisar seguranca (autorizacao, mass assignment, acoes destrutivas).
5. Otimizar requests e queries (sem N+1, sem query em loop).
6. Em volume, aplicar lote para insert/update/delete.
7. Executar testes relevantes.
8. Rodar `php artisan dev:reinstall`.

## Fonte
- `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`
- `/.ai/guidelines/projeto/padroes.md`
