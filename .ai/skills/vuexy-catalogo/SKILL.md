# Skill: Catalogo Vuexy Resources

## Objetivo
Usar o arquivo `/.ai/guidelines/projeto/vuexy-recursos-catalogo.md` como indice completo da pasta `templete/Vuexy/resources`.

## Regra principal
`templete/Vuexy/resources` e apenas referencia.
- Nao implementar regra de negocio nessa pasta.
- Implementar no codigo real (`resources/*` e `modules/Ajustatech/*`).

## Quando usar
- Quando precisar encontrar rapidamente qual arquivo Vuexy consultar para uma tela/componente.
- Quando precisar decidir entre referencia de `views/content`, `assets/js`, `assets/vendor/scss` ou `menu`.
- Quando estiver criando UI nova em Livewire/Blade e quiser manter padrao visual.

## Fluxo recomendado
1. Abrir `/.ai/guidelines/projeto/vuexy-recursos-catalogo.md`.
2. Buscar pela categoria da necessidade:
   - Layout: `views/layouts`
   - Pagina pronta: `views/content/*`
   - Modal/offcanvas: `views/_partials/*`
   - Comportamento JS: `assets/js/*`
   - Tema/estilo: `assets/vendor/scss/*`
   - Dependencia externa: `assets/vendor/libs/*`
3. Copiar apenas o padrao visual/tecnico necessario.
4. Aplicar no modulo real do projeto.

## Nao fazer
- Nao mover arquivos do template para producao sem revisao.
- Nao criar acoplamento de negocio com arquivos de referencia.
