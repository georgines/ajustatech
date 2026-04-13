# Filtros de Tabela em Modulos

## Regra minima
Toda listagem em tabela deve ter, no minimo:
1. busca textual,
2. limite por pagina,
3. filtro de data quando fizer sentido,
4. filtros de dominio (status/tipo/categoria etc.).

## Performance
- Aplicar filtro no banco, nunca em colecao carregada.
- Usar paginacao real.
- Evitar N+1 com eager loading quando houver relacao.
- Criar indices para colunas filtradas com frequencia.

## UX
- Busca com debounce.
- Estado vazio claro.
- Filtros legiveis em mobile e desktop.

## Checklist rapido
1. Busca e filtros funcionam no banco?
2. Paginacao aplicada?
3. Sem N+1 na listagem?
4. Testes cobrem filtros principais?
