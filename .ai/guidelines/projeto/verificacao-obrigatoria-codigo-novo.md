# Verificacao Obrigatoria para Todo Codigo Novo

## Objetivo
Definir um gate padrao para qualquer codigo novo ou alteracao relevante no projeto, evitando entregas sem validacao, sem seguranca, sem testes ou com regressao de performance.

## Regra principal
Todo codigo novo deve passar obrigatoriamente por seis verificacoes:
1. Arquitetura.
2. Validacao e sanitizacao.
3. Seguranca.
4. Performance de requisicoes e banco.
5. Testes.
6. Restauracao do ambiente apos testes.

## 1. Arquitetura
- A implementacao respeita a arquitetura modular em `modules/Ajustatech/*`?
- A responsabilidade ficou no lugar correto?
  - Model em `Database/Models`
  - Service como orquestrador
  - Consulta no Model
  - View sem regra de negocio
- Se for funcionalidade nova dentro de modulo existente, a separacao por funcionalidade foi preservada?

Fonte complementar:
- `/.ai/guidelines/projeto/arquitetura.md`
- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/servicos-e-funcionalidades-modulares.md`

## 2. Validacao e Sanitizacao
- Todo input do usuario foi sanitizado no backend?
- Todo input relevante foi validado no backend?
- O frontend recebeu restricoes basicas quando aplicavel?
- Regras condicionais de negocio foram refletidas na validacao?
- A implementacao evita normalizar silenciosamente erro que deveria falhar?

Fonte complementar:
- `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`
- `/.ai/guidelines/projeto/validacoes-core-reutilizaveis.md`

## 3. Seguranca
- Existe risco de mass assignment?
- Existe propriedade publica sensivel em Livewire sem protecao?
- Existe identificador sensivel vindo do cliente sem `Locked`, model property ou autorizacao?
- Existe acao destrutiva ou de persistencia sem validacao/autorizacao correspondente?
- Existe interpolacao insegura, URL nao validada ou HTML bruto desnecessario?

### Regras adicionais para Livewire
- Toda propriedade publica deve ser tratada como entrada nao confiavel.
- Estados sensiveis como `mode`, IDs, contexto de recurso e chaves de relacao devem ser travados ou revalidados.
- Fluxos de salvar, editar, excluir e atualizar estado persistente devem ter testes de tampering quando houver superficie sensivel.

Fonte complementar:
- `/.ai/guidelines/projeto/seguranca.md`
- `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`

## 4. Performance de Requisicoes e Banco
- O fluxo evita request ao backend para acao puramente visual?
- Cada acao de negocio esta resolvida com o menor numero razoavel de requests?
- Nao ha query dentro de loop?
- Nao ha N+1 em listagem, carregamento, edicao ou exclusao?
- Quando o fluxo e critico, existe teste de contagem de requests/queries?

### Meta pratica
- Fluxo visual: 0 request quando possivel.
- Acao de negocio confirmada: 1 request efetivo quando possivel.
- Query count estabilizada em metodos criticos, sem crescimento por item quando lote/eager loading resolve.

Fonte complementar:
- `/.ai/guidelines/projeto/otimizacao-solicitacoes.md`
- `/.ai/guidelines/projeto/seeds-em-lote-performance.md`

## 5. Testes
- Existe teste de Feature criado antes da implementacao quando for feature nova?
- Foram cobertos cenarios validos?
- Foram cobertos cenarios invalidos?
- Foram cobertos cenarios de seguranca quando aplicavel?
- Foram cobertos cenarios de performance/requisicoes/queries quando aplicavel?
- Se houver persistencia, existe assert de banco?

### Matriz minima por mudanca
- Input novo: teste valido + invalido.
- Livewire sensivel: teste de tampering + fluxo principal.
- Query critica: teste de contagem de queries.
- Modelo novo: teste de banco + factory + seeder.

Fonte complementar:
- `/.ai/guidelines/projeto/testes-feature-tdd.md`
- `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md`
- `/.ai/guidelines/projeto/modelos-separacao-e-cobertura.md`

## 6. Restauracao do Ambiente apos Testes
- Depois de executar testes, rodar obrigatoriamente `php artisan dev:reinstall`.
- Nao considerar a task concluida sem o ambiente recomposto para validacao manual local.

Fonte complementar:
- `/.ai/guidelines/projeto/pos-testes-dev-reinstall.md`

## Checklist final obrigatorio
1. O codigo esta no lugar arquitetural correto?
2. Toda entrada foi sanitizada e validada?
3. A superficie sensivel foi protegida?
4. Requisicoes e queries foram otimizadas?
5. Os testes cobrem comportamento, erro, seguranca e performance quando necessario?
6. `php artisan dev:reinstall` foi executado apos os testes?

## Criterio de pronto
Se qualquer item acima falhar, o codigo novo nao esta pronto.
