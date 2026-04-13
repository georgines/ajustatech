# Comandos do Core

## Comandos de uso diario
- `php artisan dev:clear`
  - Limpa caches de desenvolvimento.
- `php artisan dev:migrate`
  - Executa `migrate:fresh`.
- `php artisan dev:seed`
  - Executa seeds modulares via `module:seed`.
- `php artisan dev:reinstall`
  - Reconstroi banco + caches + seeds para ambiente local consistente.

## Seeds modulares
- `php artisan module:seed`
  - Executa todos comandos `module:seed-*` registrados.

## Scaffolding
- `php artisan make:module {Nome}`
  - Gera estrutura base completa do modulo.
- Comandos `make:module-*` existem, mas sao internos na maior parte dos fluxos.

## Fluxo recomendado de ambiente
1. `php artisan dev:clear`
2. `php artisan dev:migrate`
3. `php artisan dev:seed`
4. `php artisan dev:reinstall`
