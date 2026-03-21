# Arquitetura do Projeto Ajustatech

## Regra de Ouro
Este projeto **nao segue a estrutura padrao monolitica do Laravel para features de dominio**.
A arquitetura principal e **modular**, com modulos em `modules/Ajustatech/*`.

## Estrutura Base
- Aplicacao base Laravel 11 + Livewire 3.
- Template visual baseado em Vuexy.
- Modulos ativos:
  - `modules/Ajustatech/Core`
  - `modules/Ajustatech/Customer`
  - `modules/Ajustatech/Financial`

## Papel de cada modulo
- `Core`:
  - Orquestra providers centrais.
  - Registra comandos de scaffolding e comandos `dev:*`.
  - Disponibiliza `MenuManager`, `MenuRouteResolver`, `SwitchAlertDispatch`, regras CPF/CNPJ.
  - Fornece stubs para geracao de novos modulos.
- `Customer`, `Financial` e novo modulo que for criado:
  - Cada modulo contem seu proprio ciclo completo: `Commands`, `Database` (Factories/Migrations/Models/Seeders), `Lang` (en e pt-BR), `Livewire`, `Menu`(horizontalMenu.json e verticalMenu.json), `Providers`, `Routes`, `Tests`, `Views` e `composer.json`.

## Bootstrap e Registro
- Provider modular raiz: `Ajustatech\Core\Providers\CoreServiceProvider` em `bootstrap/providers.php`.
- `CoreServiceProvider` registra:
  - `CommandServiceProvider`
  - `MenuServiceProvider`
  - Modulos de dominio (ex.: `CustomerServiceProvider`, `FinancialServiceProvider`)
  - `ViewServiceProvider` (deve permanecer como ultimo, conforme convencao dos comandos do Core).

## Namespace e Autoload
O `composer.json` raiz usa PSR-4 para modulos:
- `Ajustatech\Core\` -> `modules/Ajustatech/Core/src`
- `Ajustatech\Customer\` -> `modules/Ajustatech/Customer/src`
- `Ajustatech\Financial\` -> `modules/Ajustatech/Financial/src`

## Menus modulares
- Cada modulo publica JSONs de menu (`Menu/verticalMenu.json` e `Menu/horizontalMenu.json`).
- `MenuManager` agrega menus dos modulos.
- `MenuRouteResolver` tenta resolver `slug` em rotas e ajusta URL relativa quando a rota existe.
- `ViewServiceProvider` compartilha `menuData` globalmente para os layouts Vuexy.

## Importante sobre Vuexy (Referencia)
A pasta `templete/Vuexy/resources` e **somente referencia**.
- Nao editar para implementar feature real.
- Implementacao real deve ocorrer em `resources/*` e/ou `modules/Ajustatech/*`.
- Objetivo: manter padrao visual e estrutural, sem acoplamento direto ao template de referencia.
