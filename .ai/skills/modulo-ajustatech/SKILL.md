# Skill: Modulo Ajustatech

## Objetivo
Ajudar a criar ou evoluir modulos no padrao **Ajustatech modular** (Laravel + Livewire), respeitando os comandos e stubs do `Core`.

## Aviso de Referencia
A pasta `templete/Vuexy/resources` e **somente referencia** de UI.
Implementacoes reais devem ocorrer em `resources/*` e `modules/Ajustatech/*`.

## Quando usar
- Criar modulo novo.
- Criar entidade nova dentro de modulo existente.
- Ajustar provider, rotas, menus, composer/autoload e testes modulares.

## Checklist Operacional
1. Confirmar nome do modulo/entidade.
2. Preferir comandos do Core:
   - `php artisan make:module <Nome>`
   - `php artisan dev:clear`
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
   - `php artisan module:seed`
   - `php artisan make:module-model <Nome> <path>` (uso avancado, quando aplicavel)
3. Conferir estrutura criada em `modules/Ajustatech/<Modulo>/src`.
4. Registrar provider no `CoreServiceProvider`.
5. Registrar namespace PSR-4 no `composer.json` raiz.
6. Rodar `composer dumpautoload`.
7. Garantir rotas nomeadas no modulo.
8. Garantir menus (`Menu/*.json`) com `slug` alinhado as rotas.
9. Garantir Livewire components registrados no provider do modulo.
10. Adicionar/ajustar testes de modulo.

## Padrões obrigatorios
- Namespace `Ajustatech\<Modulo>\...`.
- `#[Layout('core::layouts.app')]` em componentes Livewire de modulo.
- Traducoes em `Lang/en` e `Lang/pt-BR`.
- Seeds por comando `module:seed-*`.

## Validacoes finais
- `php artisan test`
- `php artisan route:list` (rotas do modulo presentes)
- `php artisan dev:migrate`
- `php artisan dev:seed`

## Nao fazer
- Nao implementar regra de negocio em `templete/`.
- Nao quebrar padrao de provider final (`ViewServiceProvider` por ultimo no Core).
- Nao deixar modulo sem `composer.json` proprio e sem registro PSR-4 no composer raiz.
- Nao usar comandos `make:module-*` internos sem entender os argumentos obrigatorios de scaffolding.
