# Arquitetura do Projeto Ajustatech

## Resumo
- Base: Laravel 11 + Livewire 3 + Vuexy (referencia visual).
- Arquitetura de dominio: modular em `modules/Ajustatech/*`.
- Bootstrap principal: `Ajustatech\Core\Providers\CoreServiceProvider`.

## Modulos ativos
- `Core`: orquestracao (providers, comandos `dev:*`, menu manager, regras reutilizaveis).
- `Customer`, `Financial`, `ServiceOrder`, `Settings`: dominio com ciclo completo (Models, Livewire, Services, Tests, Seeders).

## Regra de implementacao
- Feature de dominio nova nasce em modulo, nao em `app/`.
- Cada modulo deve manter estrutura previsivel: `Database`, `Livewire`, `Services`, `Providers`, `Routes`, `Tests`, `Lang`, `Views`.
- Quando houver multiplas features, separar por funcionalidade (ex.: `EquipmentType`, `Procedure`, `Analysis`).

## Providers e ordem
- `CoreServiceProvider` registra providers dos modulos.
- `ViewServiceProvider` deve permanecer por ultimo no registro.

## Menus e rotas
- Menus por modulo em JSON (`Menu/verticalMenu.json`, `Menu/horizontalMenu.json`).
- `slug` do menu deve apontar para rota nomeada existente.

## Vuexy
- `templete/Vuexy/resources` e somente referencia de UI.
- Implementacao real sempre em `resources/*` e `modules/Ajustatech/*`.
