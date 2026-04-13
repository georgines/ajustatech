# Padrao de Botoes de Acao em Tabelas

## Padrao visual
- Usar `btn btn-sm btn-icon` para acoes por linha.
- Icon-only com `title` e `aria-label` obrigatorios.
- Coluna de acoes sempre no final da tabela.

## Icones
- Biblioteca padrao: Tabler (`ti ti-*`).
- Sem mistura de bibliotecas na mesma tabela.
- Padrao semantico:
  - editar: `ti ti-pencil`
  - excluir: `ti ti-trash`
  - ajuda: `ti ti-help-circle`

## Acessibilidade e responsividade
- Todo botao icon-only com `title` + `aria-label`.
- Manter `btn-sm btn-icon` em mobile para reduzir largura.
