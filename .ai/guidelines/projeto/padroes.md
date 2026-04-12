# Padroes de Implementacao (Fonte Principal)

## 1. Escopo arquitetural
- Dominio novo sempre em `modules/Ajustatech/<Modulo>/src`.
- Nao implementar regra de negocio de modulo em `app/`.
- Providers de modulo devem ser registrados no Core.

## 2. Separacao de responsabilidade
- Model: `Database/Models` (relacoes, scopes, consultas de dominio, persistencia do modelo).
- Service: orquestracao de caso de uso (consome Model e contratos).
- Livewire/View: estado e interface, sem regra de dominio pesada.

## 3. Banco e performance (obrigatorio)
- Nao executar query em loop.
- Sempre considerar relacionamentos para evitar N+1 (`with`, `withCount`, `whereHas` quando aplicavel).
- Operacoes de volume devem usar lote:
  - inserir: `insert`
  - atualizar: `upsert` (com chave unica valida)
  - excluir: `whereIn(...)->delete()`

## 4. Validacao e i18n de erro
- Todo input deve ter sanitizacao + validacao backend.
- Mensagens de validacao para usuario final devem estar em portugues (pt-BR).
- Textos de modulo devem usar `Lang/en` e `Lang/pt-BR` com `trans()`.

## 5. Seeds, factories e testes
- Todo modelo relevante deve ter migration, factory e seeder.
- Seeds devem respeitar relacionamentos e cobrir cenarios reais.
- Fluxo de testes: RED -> GREEN -> REFACTOR.
- Apos testes, executar `php artisan dev:reinstall`.

## 6. Livewire
- Componentes com `#[Layout('core::layouts.app')]` quando aplicavel.
- Em UI visual (abrir modal, ajuda), evitar request backend desnecessaria.
- Acoes de negocio (salvar/editar/excluir): fluxo enxuto e previsivel.

## 7. Laravel da versao do projeto
- Seguir padroes oficiais do Laravel 11 (versao declarada no `composer.json`).
- Evitar abordagem legada/deprecada quando houver alternativa oficial atual.

## 8. Referencias complementares
- `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`
- `/.ai/guidelines/projeto/servicos-e-funcionalidades-modulares.md`
- `/.ai/guidelines/projeto/comandos-core.md`
- `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`
- `/.ai/guidelines/projeto/testes-feature-tdd.md`
