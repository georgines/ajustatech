# Skill: Otimizacao de Solicitacoes

## Objetivo
Aplicar padrao de eficiencia de requisicoes em toda funcionalidade nova.

## Quando usar
- Ao criar listagens com tabela e botoes de acao.
- Ao criar modais de ajuda, preview ou detalhes.
- Ao ajustar fluxos de excluir/salvar/confirmar em Livewire.

## Fonte de verdade
- `/.ai/guidelines/projeto/otimizacao-solicitacoes.md`

## Procedimento obrigatorio
1. Mapear solicitacoes por acao:
   - carregar lista
   - abrir ajuda/detalhe
   - editar/excluir/salvar
2. Classificar cada acao:
   - visual/local (deve evitar backend)
   - negocio/persistencia (deve chamar backend)
3. Reduzir round-trips:
   - mover estado visual para cliente quando possivel
   - manter 1 chamada por acao de negocio confirmada
4. Revisar lista para evitar N+1.
5. Validar comportamento final em testes e navegacao real.

## Criterios de aceite
- Carregamento inicial com solicitacao minima necessaria.
- Acoes de apoio visual sem request desnecessario.
- Excluir/editar/salvar com fluxo enxuto.
- Sem degradacao funcional ou regressao de UX.

## Nao fazer
- Nao disparar request para abrir modal com dados ja disponiveis.
- Nao usar dois eventos para uma unica confirmacao simples.
- Nao aceitar N+1 em listagens novas.
