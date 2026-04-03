# Padroes de Implementacao

## Regra Geral
Toda feature de dominio nova deve nascer em modulo (`modules/Ajustatech/<Modulo>/src`) e nao em `app/`.

## Fluxo preferencial para novo modulo
1. Executar `php artisan make:module NomeModulo`.
2. Revisar instrucoes exibidas pelo comando.
3. Registrar provider do modulo no `CoreServiceProvider` (antes do `ViewServiceProvider`).
4. Adicionar namespace PSR-4 no `composer.json` raiz.
5. Rodar `composer dumpautoload`.
6. Garantir diretivas de teste no `phpunit.xml` para o modulo.

## Estrutura minima esperada por modulo
- `Commands/`
- `Database/Factories`
- `Database/Migrations`
- `Database/Models`
- `Database/Seeders`
- `Lang/en` e `Lang/pt-BR`
- `Livewire/`
- `Menu/`
- `Providers/`
- `Routes/`
- `Tests/Feature` e `Tests/Unit`
- `Views/livewire`
- `composer.json`

## Convencoes Livewire
- Componentes com `#[Layout('core::layouts.app')]`.
- Par de componentes por entidade:
  - `Show<Entidade>`
  - `<Entidade>Management`
- Rotas tipicas:
  - listagem/show
  - cadastro
  - edicao
- Para telas de listagem em tabela, seguir:
  - `/.ai/guidelines/projeto/filtros-tabelas-modulo.md`.

## Convencoes de comandos internos
- Seeds modulares: `module:seed-*`.
- Orquestracao de seeds:
  - `php artisan module:seed` (executa todos `module:seed-*`).
  - `php artisan dev:seed` (delegando para `module:seed`).
- Migracao de desenvolvimento:
  - `php artisan dev:migrate` (atual: `migrate:fresh`).
- Limpeza de cache de desenvolvimento:
  - `php artisan dev:clear`.
- Comando recomendado para gerar modulo completo:
  - `php artisan make:module <NomeModulo>`.
- Comandos `make:module-*` existem e sao validos, mas sao internos de scaffolding na maior parte dos fluxos.
- Fonte de verdade dos comandos do Core:
  - `/.ai/guidelines/projeto/comandos-core.md`.
- Convencao de seeds obrigatorios:
  - `/.ai/guidelines/projeto/seeds-obrigatorios.md`.
- Convencao profissional de migrations:
  - `/.ai/guidelines/projeto/migrations-laravel-profissional.md`.

## Convencoes de migrations (obrigatorio)
- Seguir `/.ai/guidelines/projeto/migrations-laravel-profissional.md`.
- Em MySQL, sempre considerar limite de 64 caracteres para nome de indice/constraint.
- Para indices compostos e FKs com nomes longos, definir nome explicito curto e descritivo.

## Convencoes de menu
- Cada item deve ter `slug` que corresponda a nome de rota.
- Quando rota existe, `MenuRouteResolver` substitui URL final com base no `slug`.
- Evitar URL hardcoded quando houver rota nomeada.

## Convencoes de i18n
- Modulos devem concentrar textos em `Lang/<locale>/messages.php` e arquivos correlatos.
- Priorizar `trans('modulo::arquivo.chave')` em componentes e views.

## Convencoes de validacao
- Validacoes reutilizaveis devem ser centralizadas em:
  - `modules/Ajustatech/Core/src/Rules`
- Quando nao existir regra pronta, criar no Core e reutilizar no modulo que precisar.
- Fonte de verdade:
  - `/.ai/guidelines/projeto/validacoes-core-reutilizaveis.md`.

## Convencoes de testes (obrigatorio)
- Fluxo TDD para feature: RED -> GREEN -> REFACTOR.
- Antes de implementar, criar teste de **Feature** e comprovar falha inicial.
- Implementar somente depois da falha validada.
- Concluir apenas com teste de Feature passando.
- Fonte de verdade para esse fluxo:
  - `/.ai/guidelines/projeto/testes-feature-tdd.md`.
- Factories + Seeds + Testes:
  - `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md`.

## Convencoes de responsividade (obrigatorio)
- Pensar primeiro na visualizacao em dispositivo movel (mobile-first).
- Depois ajustar e validar a experiencia completa para desktop/PC.
- Toda tela, componente e fluxo novo deve funcionar nos dois modos de exibicao: movel e desktop.
- Nao considerar implementacao concluida sem verificacao visual e funcional em ambos os contextos.

## Referencias Vuexy (somente referencia)
- Use `templete/Vuexy/resources` para copiar padrao visual e blocos Blade/SCSS/JS.
- Qualquer adaptacao deve ser feita no codigo real do projeto.
- Nao tratar arquivos de `templete/` como fonte de verdade de negocio.
