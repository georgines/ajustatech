# Skill: Padrao de Botoes de Acao em Tabelas

## Objetivo
Aplicar padrao unico para botoes de acao e icones em listagens de tabela.

## Quando usar
- Ao criar ou ajustar colunas de acoes em tabelas.
- Ao adicionar botao de ajuda, editar, excluir ou similares em listagens.
- Ao revisar consistencia visual de icones em modulos diferentes.

## Fonte de verdade
- `/.ai/guidelines/projeto/padrao-botoes-acao-tabelas-e-icones.md`

## Checklist Operacional
1. Garantir coluna de acoes na tabela com botoes `btn btn-sm btn-icon`.
2. Garantir icones `ti ti-*` com `text-primary` por padrao.
3. Garantir `title` e `aria-label` em todos os botoes icon-only.
4. Para ajuda, usar icone de interrogacao (`ti ti-help-circle`).
5. Evitar conteudo pesado dentro da celula; abrir modal/detalhe contextual.
6. Validar em mobile e desktop.

## Nao fazer
- Nao misturar bibliotecas de icones na mesma tabela.
- Nao usar botao de acao sem `aria-label`.
- Nao substituir botoes icon-only por links textuais longos em celulas de acao.
