---
name: feature-test-first
description: "Use quando desenvolver uma nova feature com TDD, escrevendo testes de Feature primeiro e validando o fluxo RED -> GREEN."
---

# Skill: Feature Test First

## Objetivo
Garantir fluxo TDD com testes de **Feature**: primeiro falha, depois implementa, por fim passa.

## Regra obrigatoria
Para cada feature nova:
1. escrever teste de Feature primeiro;
2. rodar e comprovar falha (RED);
3. implementar;
4. rodar e comprovar sucesso (GREEN).

## Onde criar testes
- Modulos:
  - `modules/Ajustatech/<Modulo>/src/Tests/Feature/...`
- App base:
  - `tests/Feature/...`

## Fluxo pratico
1. Definir cenario e nome do teste.
2. Criar teste de Feature cobrindo comportamento esperado.
3. Rodar somente esse teste e registrar falha inicial.
4. Implementar a feature com a menor mudanca necessaria.
5. Rodar o mesmo teste ate ficar verde.
6. Rodar testes relacionados para evitar regressao.

## Comandos padrao
- `php artisan test --testsuite=Feature`
- `php artisan test <caminho-do-teste>`

## Nao fazer
- Nao implementar feature antes do teste de Feature.
- Nao fechar tarefa sem evidenciar RED -> GREEN.
- Nao substituir Feature Test por Unit Test quando o objetivo for comportamento de feature.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

