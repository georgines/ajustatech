# Skill: Modelos Completos e Testes

## Objetivo
Forcar padrao de modelo no lugar correto, impedir logica de modelo fora de `Database/Models` e exigir migration, factory, seeder e testes (feature + unit).

## Quando usar
- Criacao de modelo novo em qualquer modulo.
- Refatoracao onde regras de modelo estejam espalhadas fora de `Database/Models`.
- Implementacao de funcionalidade que toca persistencia de dominio.

## Fonte de verdade
- `/.ai/guidelines/projeto/modelos-separacao-e-cobertura.md`
- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/testes-feature-tdd.md`
- `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md`

## Checklist Operacional
1. Confirmar que o modelo esta em `Database/Models`.
2. Remover logica de modelo de componentes/services/controllers quando houver.
3. Garantir que consultas de dados (exibir, carregar, editar e excluir) fiquem nos Models, nao no Service.
4. Otimizar queries e eliminar query em loop.
5. Garantir migration, factory e seeder para o modelo.
6. Aplicar o fluxo de testes conforme guidelines oficiais (Feature + cobertura complementar).
7. Garantir que factory/seeder respeitam relacionamentos e preenchem dados relacionais corretamente.
8. Cobrir funcionalidades do modulo/submodulo relacionadas ao modelo.
9. Executar suite relevante, incluindo testes de requisicoes/performance, e validar resultado.

## Nao fazer
- Nao colocar regra de modelo em arquivos de orquestracao (Livewire/Service/Controller).
- Nao deixar consulta de banco no Service quando pertencer ao Model.
- Nao entregar modelo sem migration, factory e seeder.
- Nao duplicar regras de teste nesta skill: seguir os guidelines oficiais de testes/factories.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

