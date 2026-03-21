# Comandos do Core (Fonte de Verdade)

Este arquivo descreve os comandos registrados em `Ajustatech\Core\Providers\CommandServiceProvider`.

## Comandos para uso direto
- `php artisan dev:clear`
  - Limpa caches principais (`clear-compiled`, `cache:clear`, `config:clear`, `queue:clear`, `schedule:clear-cache`, `view:clear`).
- `php artisan dev:migrate`
  - Executa `migrate:fresh`.
- `php artisan module:seed`
  - Procura e executa todos os comandos com prefixo `module:seed-*`.
- `php artisan dev:seed`
  - Delega para `module:seed`.
- `php artisan make:module {name} {--f|force}`
  - Gera estrutura base completa de modulo usando os stubs e comandos internos.

## Comandos internos de scaffolding
Estes comandos existem e estao registrados, mas normalmente sao chamados automaticamente por `make:module`:

- `php artisan make:module-provider {name} {path} {namespace-import} {component-register} {--f|force}`
- `php artisan make:module-menu {name} {path} {--f|force}`
- `php artisan make:module-routes {name} {path} {namespace-import} {route-definition} {--f|force}`
- `php artisan make:module-model {name} {path} {--f|force}`
- `php artisan make:module-livewire-route-components {name} {path} {--f|force}`
- `php artisan make:module-composer {name} {path} {--f|force}`

## Observacoes importantes
- Em fluxo normal, prefira `make:module` ao inves de chamar cada comando interno manualmente.
- Em ambiente de desenvolvimento, o ciclo usual e:
  - `php artisan dev:clear`
  - `php artisan dev:migrate`
  - `php artisan dev:seed`
- A pasta `templete/Vuexy/resources` continua sendo somente referencia de UI.

