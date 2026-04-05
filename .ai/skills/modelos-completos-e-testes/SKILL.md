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
3. Garantir migration, factory e seeder para o modelo.
4. Aplicar o fluxo de testes conforme guidelines oficiais (Feature + cobertura complementar).
5. Executar suite relevante e validar resultado.

## Nao fazer
- Nao colocar regra de modelo em arquivos de orquestracao (Livewire/Service/Controller).
- Nao entregar modelo sem migration, factory e seeder.
- Nao duplicar regras de teste nesta skill: seguir os guidelines oficiais de testes/factories.
