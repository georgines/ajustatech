# Otimizacao de Solicitacoes (HTTP/Livewire)

## Objetivo
Reduzir round-trips desnecessarios e manter telas novas com carregamento e interacoes eficientes.

## Regra obrigatoria para codigo novo
Toda feature nova deve ser revisada com foco em quantidade de solicitacoes:
1. Carregamento da listagem/pagina.
2. Acoes de apoio (ajuda, preview, abrir modal, expandir detalhes).
3. Acoes de negocio (salvar, editar, excluir, confirmar).
4. Quantidade de queries de banco em exibicao, carregamento, edicao e exclusao.

## Metas praticas
- Carregamento inicial de listagem: 1 solicitacao de dados principal.
- Acoes de apoio visual: preferir 0 solicitacoes ao backend (estado local).
- Acoes destrutivas/negocio: 1 solicitacao efetiva por acao confirmada.
- Evitar fluxo em 2 chamadas quando 1 chamada resolve.

## Padroes recomendados
- Passar dados necessarios da linha para modal via estado local (Alpine/JS), sem refetch.
- Evitar buscar no backend ao clicar "ajuda" se os dados ja estao na tabela.
- Fazer confirmacao no cliente quando possivel e chamar backend apenas no confirmar.
- Consolidar recarga de lista apos operacao apenas quando necessario.
- Em Livewire, evitar `wire:click` que so abre UI sem necessidade de servidor.
- Centralizar consultas nos Models para facilitar reutilizacao e controle de performance.
- Em editar/excluir, evitar refetch desnecessario de dados ja disponiveis.

## Anti-padroes
- Clique em botao de ajuda disparando query no backend sem necessidade.
- Confirmacao remota seguida de nova chamada para executar a mesma acao.
- N+1 em listagens sem eager loading quando houver relacionamentos.
- Re-render completo para atualizar apenas estado visual local.

## Checklist de conclusao (obrigatorio)
1. Foi mapeada a quantidade de solicitacoes por fluxo principal?
2. Existe clique de apoio visual sem request ao backend quando possivel?
3. Cada acao de negocio gera apenas 1 solicitacao efetiva?
4. Nao ha N+1 no carregamento da lista?
5. O comportamento foi validado em desktop e mobile?
6. Foram executados testes de requisicoes/performance para medir e validar a otimizacao dos fluxos de exibir, carregar, editar e excluir?
