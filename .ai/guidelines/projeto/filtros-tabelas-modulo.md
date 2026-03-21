# Filtros de Tabela em Modulos de Visao

## Objetivo
Padronizar telas de listagem (tabelas) para que sejam:
- rapidas no banco de dados;
- usaveis para o usuario;
- consistentes entre modulos.

## Regra obrigatoria
Sempre que um modulo criar tela para exibir varios dados em tabela, deve implementar filtros relevantes para o recurso.

No minimo, considerar:
1. busca textual;
2. total de exibicoes por pagina;
3. filtro de data (quando fizer sentido no dominio);
4. filtros de contexto do recurso (status, tipo, categoria, origem, etc.).

## Referencia real do projeto (Customer)
No modulo `modules/Ajustatech/Customer` ja existe base de comportamento:
- busca (`search`);
- filtro de status ativo (`activeonly`);
- limite por pagina (`limiteperpage`);
- debounce na busca em tela Livewire.

Esse padrao deve ser evoluido e replicado nos novos modulos de tabela.

## Diretrizes de performance (banco)
- Filtros devem ser aplicados no banco (query builder/Eloquent), nunca em colecao carregada em memoria.
- Evitar `get()` sem necessidade em listagens grandes.
- Preferir paginacao real (`paginate`/`simplePaginate`) quando houver crescimento de dados.
- Criar indices para colunas filtradas com frequencia:
  - ex.: `status`, `created_at`, `email`, `cpf_cnpj`, chaves de relacionamento.
- Em busca textual, limitar campos pesquisados aos que realmente agregam valor.
- Evitar consultas N+1 em tabelas com relacionamentos (`with()` quando necessario).

## Diretrizes de UX de filtros
- Busca com debounce para reduzir carga.
- Controle de exibicao por pagina com opcoes coerentes (ex.: 10, 30, 50, 100).
- Filtro de data:
  - intervalo (`data inicial` e `data final`) quando o recurso for temporal;
  - atalhos quando fizer sentido (hoje, ultimo mes, etc.).
- Filtros de dominio devem refletir regras de negocio reais do modulo.
- Mostrar estado vazio claro quando nenhum resultado for encontrado.

## Diretrizes tecnicas (Livewire + Modulo)
- Estado dos filtros deve ficar no componente Livewire de listagem (`Show<Entidade>`).
- Alteracao de filtro deve disparar nova consulta no banco.
- Regras de filtro devem ficar centralizadas no Model/Query (scopes/metodos), nao espalhadas na View.
- View deve apenas renderizar filtros e resultados.

## Check de implementacao
Antes de concluir uma tabela nova em modulo, confirmar:
- [ ] Busca textual funcional e aplicada no banco.
- [ ] Exibicao por pagina funcional.
- [ ] Filtro de data implementado quando necessario para o recurso.
- [ ] Filtros especificos do dominio implementados.
- [ ] Consulta otimizada (sem N+1 e sem filtragem em memoria).
- [ ] Testes de Feature cobrindo cenarios principais de filtro.

## Relacao com testes
Seguir fluxo TDD de Feature:
- primeiro criar teste de filtro;
- comprovar falha;
- implementar;
- validar sucesso.

Fonte complementar:
- `/.ai/guidelines/projeto/testes-feature-tdd.md`

