---
name: vuexy-livewire
description: "Use quando construir interfaces Blade ou Livewire com visual Vuexy, reaproveitando layouts, menus e padroes visuais sem acoplar ao template."
---

# Skill: Vuexy + Livewire (Referencia)

## Objetivo
Guiar implementacoes de UI no projeto mantendo o visual Vuexy sem copiar cegamente a referencia.

## Aviso Critico
`templete/Vuexy/resources` e **apenas referencia**.
- Nao e fonte de verdade de negocio.
- Nao deve ser tratada como codigo oficial da aplicacao.

## Quando usar
- Criacao de paginas Blade/Livewire no estilo Vuexy.
- Ajuste de layouts (`layoutMaster`, `contentNavbarLayout`, menus, navbar, footer).
- Integracao de assets SCSS/JS em `resources/assets/*`.

## Fluxo recomendado
1. Escolher referencia visual em `templete/Vuexy/resources/views`.
2. Reproduzir estrutura equivalente no codigo real (`resources/views` ou `modules/*/Views`).
3. Manter compatibilidade com `menuData` compartilhado pelo Core.
4. Usar rotas nomeadas e `slug` coerente para menu ativo.
5. Em Livewire, manter layout `core::layouts.app` para modulos.

## Regras de implementacao
- Priorizar partials/layouts existentes antes de criar novos.
- Evitar duplicacao de assets de vendor sem necessidade.
- Preservar semantica de classes do Vuexy para consistencia visual.
- Nao usar `style=""` inline; mover estilos para arquivos CSS/SCSS do codigo real do projeto.
- Toda customizacao de negocio deve ficar no modulo de dominio.

## Verificacao
- Conferir menu vertical/horizontal com `menuData`.
- Validar pagina com e sem submenu ativo.
- Validar scripts de pagina e vendor em secoes corretas (`@section('vendor-script')`, `@section('page-script')`).

## Nao fazer
- Nao editar `templete/Vuexy/resources` para entregar funcionalidade.
- Nao acoplar componentes de dominio a views de demonstração sem adaptacao.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).
