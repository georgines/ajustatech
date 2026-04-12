# Catalogo Vuexy (Mapa para IA)

## Objetivo
Ajudar a IA a localizar rapidamente referencias visuais em `templete/Vuexy/resources` sem ler o template inteiro.

## Regra de ouro
- `templete/Vuexy/resources` e somente referencia de UI.
- Implementacao real deve ficar em `resources/*` e `modules/Ajustatech/*`.

## Inventario resumido
- Total de arquivos: 538
- Extensoes principais:
  - `.php`: 192
  - `.js`: 168
  - `.scss`: 161
- Areas com mais conteudo:
  - `assets/vendor`: 228 arquivos
  - `views/content`: 152 arquivos
  - `assets/js`: 111 arquivos

## Mapa por intencao (onde procurar)

### 1. Layout base (estrutura da pagina)
- `views/layouts/layoutMaster.blade.php`
- `views/layouts/contentNavbarLayout.blade.php`
- `views/layouts/horizontalLayout.blade.php`
- `views/layouts/layoutFront.blade.php`

Use quando: precisar definir estrutura global, navbar/menu/footer e slots principais.

### 2. Blocos reutilizaveis de layout
- `views/layouts/sections/navbar/*`
- `views/layouts/sections/menu/*`
- `views/layouts/sections/footer/*`
- `views/layouts/sections/scripts*.blade.php`
- `views/layouts/sections/styles*.blade.php`

Use quando: ajustar apenas parte da moldura (menu, scripts, estilos, footer, navbar).

### 3. Menus de exemplo
- `menu/verticalMenu.json`
- `menu/horizontalMenu.json`

Use quando: modelar estrutura de navegacao e hierarquia visual.

### 4. Paginas de exemplo (Blade)
Raiz: `views/content/*`

Subareas disponiveis:
- `apps`
- `authentications`
- `cards`
- `charts`
- `dashboard`
- `extended-ui`
- `form-elements`
- `form-layout`
- `form-validation`
- `form-wizard`
- `front-pages`
- `icons`
- `laravel-example`
- `layouts-example`
- `maps`
- `modal`
- `pages`
- `tables`
- `user-interface`
- `wizard-example`

Use quando: copiar estrutura visual de tela completa por tipo de feature.

### 5. Modais e offcanvas prontos
- `views/_partials/_modals/*`
- `views/_partials/_offcanvas/*`

Use quando: precisar de padrao visual para dialogos, confirmacoes e drawers.

### 6. Scripts de comportamento por tela
Raiz: `assets/js/*`

Padroes de nome util:
- `app-*` (aplicacoes e telas de negocio)
- `pages-*` (paginas institucionais/configuracao)
- `forms-*` / `form-*` (formularios e validacoes)
- `tables-*` (datatables)
- `modal-*` / `offcanvas-*` (componentes de overlay)
- `ui-*`, `cards-*`, `charts-*`, `wizard-*`

Use quando: entender interacoes JS de um exemplo de tela especifica.

### 7. Estilos e libs de vendor
- `assets/vendor/scss/*` (tokens, componentes, paginas)
- `assets/vendor/libs/*` (bibliotecas JS/CSS de terceiros)
- `assets/vendor/js/*` (helpers globais do template)

Use quando: descobrir de onde vem estilo/comportamento de um componente especifico.

### 8. Entrada CSS/JS local de recurso
- `css/app.css`
- `js/app.js`
- `js/bootstrap.js`
- `js/laravel-user-management.js`

Use quando: checar pontos de entrada basicos da stack local do template.

## Atalho de decisao rapido
1. Precisa de layout geral? -> `views/layouts/*`.
2. Precisa de tela exemplo? -> `views/content/<categoria>/*`.
3. Precisa de modal/offcanvas? -> `views/_partials/*`.
4. Precisa do comportamento JS? -> `assets/js/*` com prefixo da tela.
5. Precisa de estilo/lib base? -> `assets/vendor/scss/*` e `assets/vendor/libs/*`.

## Comandos de busca recomendados
- Tela/Blade: `rg "keyword" templete/Vuexy/resources/views`
- Script de tela: `rg --files templete/Vuexy/resources/assets/js | rg "keyword"`
- Estilo/vendor: `rg "keyword" templete/Vuexy/resources/assets/vendor`
- Modal: `rg --files templete/Vuexy/resources/views/_partials | rg "modal|offcanvas"`

## Dependencias por arquivo
- Mapa detalhado: /.ai/guidelines/projeto/vuexy-recursos-dependencias.md 
- Este mapa lista dependencias explicitas extraidas por padrao de sintaxe (Blade/JS/SCSS/CSS).

## Observacao final
Este arquivo e um mapa de navegacao para IA. Para detalhes de um item, abrir apenas os arquivos necessarios da area mapeada acima.
