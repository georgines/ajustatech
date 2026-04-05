# Skill: Modulo Ajustatech

## Objetivo
Ajudar a criar ou evoluir modulos no padrao **Ajustatech modular** (Laravel + Livewire), respeitando os comandos e stubs do `Core`.

## Aviso de Referencia
A pasta `templete/Vuexy/resources` e **somente referencia** de UI.
Implementacoes reais devem ocorrer em `resources/*` e `modules/Ajustatech/*`.

## Quando usar
- Criar modulo novo.
- Criar entidade/funcionalidade (submodulo de recurso) dentro de modulo existente.
- Ajustar provider, rotas, menus, composer/autoload e testes modulares.

## Checklist Operacional
1. Confirmar nome do modulo/entidade.
2. Se for funcionalidade dentro de modulo existente, manter no mesmo modulo pai e separar por pasta de funcionalidade.
3. Criar provider proprio da funcionalidade em `Providers/<Funcionalidade>/<Funcionalidade>ServiceProvider.php`.
4. Registrar provider da funcionalidade no provider do modulo pai.
5. Colocar artefatos da funcionalidade em pastas proprias:
   - `Commands/<Funcionalidade>/`
   - `Database/Migrations/<Funcionalidade>/`
   - `Database/Factories/<Funcionalidade>/`
   - `Database/Seeders/<Funcionalidade>/`
   - `Livewire/<Funcionalidade>/`
   - `Views/livewire/<funcionalidade-kebab-case>/`
   - `Routes/<funcionalidade>.php`
6. Em comandos de modulo (`module:seed-*`, `module:wipe-media-*`), chamar comandos da funcionalidade (`feature:*`) manualmente com `$this->call(...)`.
7. Preferir comandos do Core:
   - `php artisan make:module <Nome>`
   - `php artisan dev:clear`
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
   - `php artisan module:seed`
   - `php artisan make:module-model <Nome> <path>` (uso avancado, quando aplicavel)
8. Conferir estrutura criada em `modules/Ajustatech/<Modulo>/src`.
9. Registrar provider do modulo no `CoreServiceProvider`.
10. Registrar namespace PSR-4 no `composer.json` raiz.
11. Rodar `composer dumpautoload`.
12. Garantir rotas nomeadas no modulo.
13. Garantir menus (`Menu/*.json`) com `slug` alinhado as rotas.
14. Garantir Livewire components registrados no provider correto (modulo ou funcionalidade).
15. Adicionar/ajustar testes de modulo/funcionalidade.

## Padroes obrigatorios
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

## Guidelines complementares
- `/.ai/guidelines/projeto/padroes.md` (fonte principal consolidada).
- `/.ai/guidelines/projeto/servicos-e-funcionalidades-modulares.md` (quando houver multiplas funcionalidades no modulo).
- `/.ai/guidelines/projeto/modelos-separacao-e-cobertura.md` (modelos em `Database/Models` e cobertura minima).
