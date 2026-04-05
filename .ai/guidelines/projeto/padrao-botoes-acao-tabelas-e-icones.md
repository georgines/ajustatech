# Padrao de Botoes de Acao em Tabelas e Icones

## Objetivo
Padronizar botoes de acao em listagens para manter consistencia visual, boa usabilidade e manutencao simples.

## Estrutura base dos botoes em tabela
- Usar botao compacto por acao: `btn btn-sm btn-icon`.
- Cada acao deve ter somente icone (sem texto visivel na celula), com `title` e `aria-label`.
- Agrupar acoes na ultima coluna da tabela.

Exemplo:
```blade
<a class="btn btn-sm btn-icon" title="Editar" aria-label="Editar">
  <i class="text-primary ti ti-pencil"></i>
</a>
<button type="button" class="btn btn-sm btn-icon" title="Excluir" aria-label="Excluir">
  <i class="text-primary ti ti-trash"></i>
</button>
```

## Botao de ajuda em tabela
- Em colunas de ajuda/suporte, usar o mesmo padrao `btn btn-sm btn-icon`.
- O icone deve representar duvida/ajuda (interrogacao), por exemplo `ti ti-help-circle`.
- A acao deve abrir modal, drawer ou detalhe contextual, nunca expandir visual pesado dentro da celula.

## Padrao de icones
- Biblioteca padrao: Tabler Icons (`ti ti-*`).
- Evitar mistura de bibliotecas de icones na mesma tabela.
- Manter iconografia semantica:
  - editar: `ti ti-pencil`
  - excluir: `ti ti-trash`
  - ajuda: `ti ti-help-circle`
- Manter classe visual consistente nos icones de acao: `text-primary`, salvo excecao explicitamente definida no contexto.

## Acessibilidade minima
- Todo botao icon-only deve ter:
  - `title`
  - `aria-label`
- Se usar tooltip, ele complementa; nao substitui `aria-label`.

## Responsividade
- Em mobile, preservar `btn-sm btn-icon` para reduzir largura da coluna de acoes.
- Evitar texto adicional em botoes de acao dentro da tabela.
