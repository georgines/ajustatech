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
5. Medir e otimizar queries de banco em exibir, carregar, editar e excluir.
6. Validar comportamento final em testes e navegacao real.
7. Executar testes de requisicoes/performance para comprovar otimizacao.

## Criterios de aceite
- Carregamento inicial com solicitacao minima necessaria.
- Acoes de apoio visual sem request desnecessario.
- Excluir/editar/salvar com fluxo enxuto.
- Sem degradacao funcional ou regressao de UX.
- Evidencia de teste de requisicoes/performance validando melhora.

## Nao fazer
- Nao disparar request para abrir modal com dados ja disponiveis.
- Nao usar dois eventos para uma unica confirmacao simples.
- Nao aceitar N+1 em listagens novas.
- Nao considerar otimizado sem medir requests/queries em teste.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

