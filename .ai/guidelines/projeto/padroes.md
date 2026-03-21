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

## Convencoes de comandos internos
- Seeds modulares: `module:seed-*`.
- Orquestracao de seeds:
  - `php artisan module:seed` (executa todos `module:seed-*`).
  - `php artisan dev:seed` (delegando para `module:seed`).
- Migracao de desenvolvimento:
  - `php artisan dev:migrate` (atual: `migrate:fresh`).

## Convencoes de menu
- Cada item deve ter `slug` que corresponda a nome de rota.
- Quando rota existe, `MenuRouteResolver` substitui URL final com base no `slug`.
- Evitar URL hardcoded quando houver rota nomeada.

## Convencoes de i18n
- Modulos devem concentrar textos em `Lang/<locale>/messages.php` e arquivos correlatos.
- Priorizar `trans('modulo::arquivo.chave')` em componentes e views.

## Convencoes de responsividade (obrigatorio)
- Pensar primeiro na visualizacao em dispositivo movel (mobile-first).
- Depois ajustar e validar a experiencia completa para desktop/PC.
- Toda tela, componente e fluxo novo deve funcionar nos dois modos de exibicao: movel e desktop.
- Nao considerar implementacao concluida sem verificacao visual e funcional em ambos os contextos.

## Referencias Vuexy (somente referencia)
- Use `templete/Vuexy/resources` para copiar padrao visual e blocos Blade/SCSS/JS.
- Qualquer adaptacao deve ser feita no codigo real do projeto.
- Nao tratar arquivos de `templete/` como fonte de verdade de negocio.
