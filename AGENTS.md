<laravel-boost-guidelines>
=== .ai/arquitetura rules ===

# Arquitetura do Projeto Ajustatech

## Regra de Ouro

Este projeto **nao segue a estrutura padrao monolitica do Laravel para features de dominio**.
A arquitetura principal e **modular**, com modulos em `modules/Ajustatech/*`.

## Estrutura Base

- Aplicacao base Laravel 11 + Livewire 3.
- Template visual baseado em Vuexy.
- Modulos ativos:
  - `modules/Ajustatech/Core`
  - `modules/Ajustatech/Customer`
  - `modules/Ajustatech/Financial`

## Papel de cada modulo

- `Core`:
  - Orquestra providers centrais.
  - Registra comandos de scaffolding e comandos `dev:*`.
  - Disponibiliza `MenuManager`, `MenuRouteResolver`, `SwitchAlertDispatch`, regras CPF/CNPJ.
  - Fornece stubs para geracao de novos modulos.
- `Customer`, `Financial` e novo modulo que for criado:
  - Cada modulo contem seu proprio ciclo completo: `Commands`, `Database` (Factories/Migrations/Models/Seeders), `Lang` (en e pt-BR), `Livewire`, `Menu`(horizontalMenu.json e verticalMenu.json), `Providers`, `Routes`, `Tests`, `Views` e `composer.json`.

## Bootstrap e Registro

- Provider modular raiz: `Ajustatech\Core\Providers\CoreServiceProvider` em `bootstrap/providers.php`.
- `CoreServiceProvider` registra:
  - `CommandServiceProvider`
  - `MenuServiceProvider`
  - Modulos de dominio (ex.: `CustomerServiceProvider`, `FinancialServiceProvider`)
  - `ViewServiceProvider` (deve permanecer como ultimo, conforme convencao dos comandos do Core).

## Namespace e Autoload

O `composer.json` raiz usa PSR-4 para modulos:
- `Ajustatech\Core\` -> `modules/Ajustatech/Core/src`
- `Ajustatech\Customer\` -> `modules/Ajustatech/Customer/src`
- `Ajustatech\Financial\` -> `modules/Ajustatech/Financial/src`

## Menus modulares

- Cada modulo publica JSONs de menu (`Menu/verticalMenu.json` e `Menu/horizontalMenu.json`).
- `MenuManager` agrega menus dos modulos.
- `MenuRouteResolver` tenta resolver `slug` em rotas e ajusta URL relativa quando a rota existe.
- `ViewServiceProvider` compartilha `menuData` globalmente para os layouts Vuexy.

## Importante sobre Vuexy (Referencia)

A pasta `templete/Vuexy/resources` e **somente referencia**.
- Nao editar para implementar feature real.
- Implementacao real deve ocorrer em `resources/*` e/ou `modules/Ajustatech/*`.
- Objetivo: manter padrao visual e estrutural, sem acoplamento direto ao template de referencia.

=== .ai/comandos-core rules ===

# Comandos do Core (Fonte de Verdade)

Este arquivo descreve os comandos registrados em `Ajustatech\Core\Providers\CommandServiceProvider`.

## Comandos para uso direto

- `php artisan dev:clear`
  - Limpa caches principais (`clear-compiled`, `cache:clear`, `config:clear`, `queue:clear`, `schedule:clear-cache`, `view:clear`).
- `php artisan dev:migrate`
  - Executa `migrate:fresh`.
- `php artisan module:seed`
  - Procura e executa todos os comandos com prefixo `module:seed-*`.
- `php artisan dev:seed`
  - Delega para `module:seed`.
- `php artisan make:module {name} {--f|force}`
  - Gera estrutura base completa de modulo usando os stubs e comandos internos.

## Comandos internos de scaffolding

Estes comandos existem e estao registrados, mas normalmente sao chamados automaticamente por `make:module`:

- `php artisan make:module-provider {name} {path} {namespace-import} {component-register} {--f|force}`
- `php artisan make:module-menu {name} {path} {--f|force}`
- `php artisan make:module-routes {name} {path} {namespace-import} {route-definition} {--f|force}`
- `php artisan make:module-model {name} {path} {--f|force}`
- `php artisan make:module-livewire-route-components {name} {path} {--f|force}`
- `php artisan make:module-composer {name} {path} {--f|force}`

## Observacoes importantes

- Em fluxo normal, prefira `make:module` ao inves de chamar cada comando interno manualmente.
- Em ambiente de desenvolvimento, o ciclo usual e:
  - `php artisan dev:clear`
  - `php artisan dev:migrate`
  - `php artisan dev:seed`
- A pasta `templete/Vuexy/resources` continua sendo somente referencia de UI.

=== .ai/factories-para-seeds-e-testes rules ===

# Factories para Seeds, Testes e Povoamento

## Regra principal

Sempre que criar ou evoluir um recurso/modulo, criar (ou atualizar) **Factories** para suportar:
- seeds do modulo;
- testes (Feature, Livewire, banco e services);
- povoamento do banco apos implementacao.

## Objetivo

- Padronizar geracao de dados de teste e desenvolvimento.
- Evitar dados manuais e cenarios incompletos.
- Aumentar cobertura e confiabilidade dos testes.

## Onde criar

- Factories do modulo:
  - `modules/Ajustatech/<Modulo>/src/Database/Factories`

## Regras de uso das factories

1. Factories devem gerar dados validos para o schema real.
2. Factories devem cobrir cenarios comuns e variacoes relevantes.
3. Seeds do modulo devem usar factories sempre que possivel.
4. Testes do modulo devem priorizar factories em vez de dados hardcoded.
5. Factories devem respeitar relacionamentos reais entre entidades (FKs validas e coerencia de dominio).
6. Evitar registros "soltos": quando houver dependencia relacional, criar/associar os registros relacionados corretamente.

## Regras para seeds

- Todo recurso novo deve ter dados seedados com variacoes de negocio.
- Seeds devem preencher dados relacionais corretamente, respeitando as relacoes do modulo/submodulo.
- Seeds devem cobrir todas as funcionalidades do modulo e das funcionalidades internas (submodulos), incluindo cenarios integrados entre entidades relacionadas.
- Sempre executar povoamento global apos implementar:
  - `php artisan dev:migrate`
  - `php artisan dev:seed`

## Regras para testes

- Cobertura esperada por recurso:
  - teste de banco (persistencia/relacoes/filtros);
  - teste de service (regras de negocio);
  - teste de Livewire (fluxo de interface/comportamento).
- Cobertura esperada por modulo/submodulo:
  - validar relacionamentos principais e dados relacionais seedados;
  - cobrir funcionalidades principais de cada feature do modulo (nao apenas um fluxo isolado).
- Fluxo TDD:
  - primeiro teste falha;
  - depois implementa;
  - por fim teste passa.

## Criterio de pronto

Uma implementacao so e considerada pronta quando:
- factory do recurso existe e esta valida;
- seed usa factory e foi executado;
- relacionamentos e dados relacionais foram preenchidos corretamente;
- funcionalidades do modulo/submodulo estao cobertas por seeds e testes relevantes;
- testes de Feature (Livewire/banco/services) estao cobrindo o recurso;
- povoamento global dos modulos foi executado sem erro.

=== .ai/filtros-tabelas-modulo rules ===

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

=== .ai/migrations-laravel-profissional rules ===

# Migrations Laravel Profissionais

## Objetivo

Padronizar migrations no estilo Laravel profissional, com foco em:
- clareza e manutencao;
- comportamento deterministico em qualquer ambiente;
- prevencao do erro MySQL 1059 (limite de 64 caracteres em identificadores).

## Regras obrigatorias

1. `up()` deve descrever criacao/alteracao de schema de forma direta.
2. `down()` deve desfazer de forma direta e consistente com `up()`.
3. Evitar gambiarras na migration base:
   - nao usar `if (Schema::hasTable(...))` como regra padrao;
   - nao usar consulta em `information_schema` dentro de migrations normais;
   - nao adicionar logica de "estado parcial" na migration original.
4. Se existir banco inconsistente em desenvolvimento, corrigir com fluxo de ambiente (`dev:migrate`) ou migration corretiva separada quando realmente necessario.

## Nomeacao de indices e chaves (anti-1059)

MySQL limita nome de indice/constraint a 64 caracteres.
Sempre definir nome explicito para:
- indices compostos (`$table->index([...], 'nome_curto')`);
- foreign keys com tabela/coluna longas (`$table->foreign(..., 'nome_curto')`).

## Convencao de nomes descritivos e curtos

Usar formato:
- indice: `<sigla_tabela>_<campos_principais>_idx`
- foreign key: `<sigla_tabela>_<coluna_relacao>_fk`

Regras:
1. Nome deve ser descritivo do contexto real.
2. Nome deve ficar <= 55 caracteres (margem de seguranca).
3. Usar sigla da tabela para reduzir tamanho sem perder leitura.
4. Evitar nomes genericos como `idx1`, `fk_temp`.

## Exemplos

- `financial_cash_flow_routes` + (`flow_key`, `payment_method_type`, `is_active`)
  - bom: `fcfr_flow_paytype_active_idx`
- `financial_payment_method_costs.financial_payment_method_id`
  - bom: `fpm_costs_payment_method_fk`

## Checklist rapido antes de finalizar

1. Existe indice composto sem nome explicito? Se sim, nomear.
2. Existe FK com chance de nome longo auto-gerado? Se sim, nomear.
3. `up()` e `down()` estao simples, simetricos e sem workaround?
4. Rodou:
   - `php artisan dev:migrate`
   - `php artisan test`

=== .ai/modelos-separacao-e-cobertura rules ===

# Modelos Separados e Cobertura Obrigatoria

## Objetivo

Garantir separacao de responsabilidades do dominio e padrao minimo de persistencia e testes para todo modelo de modulo.

## Regra 1: Modelo vive em Models

Modelos de dominio do modulo devem ficar exclusivamente em:
- `modules/Ajustatech/<Modulo>/src/Database/Models`

Nao colocar codigo de modelo em:
- `Livewire/`
- `Services/`
- `Controllers/`
- `Commands/`
- qualquer outro arquivo que nao seja responsavel por modelo.

## Regra 2: Sem logica de modelo fora do modelo

Responsabilidades de modelo (relacionamentos, casts, scopes, accessors/mutators e regras de persistencia do proprio modelo) devem permanecer no arquivo do modelo.

Services, componentes e controllers podem orquestrar fluxo, mas nao devem replicar comportamento interno do modelo.

Inclui obrigatoriamente:
- consultas de leitura/listagem;
- consultas para carregamento de dados de edicao;
- consultas para fluxo de exclusao;
- consultas para carregamento de relacoes.

Services nao devem concentrar consultas de dados; devem chamar metodos/scopes do Model.

## Regra 3: Todo modelo deve ser completo

Para cada modelo novo ou relevante do modulo, garantir:
1. Migration correspondente.
2. Factory correspondente.
3. Seeder correspondente.

Estrutura esperada:
- `Database/Migrations/*create_<tabela>_table.php`
- `Database/Factories/<Model>Factory.php`
- `Database/Seeders/<Model>Seeder.php` (ou seeder equivalente por funcionalidade)

## Regra 4: Testes e seeds seguem fontes oficiais

Para evitar duplicidade de regra, usar como fonte oficial:
- `/.ai/guidelines/projeto/testes-feature-tdd.md` (fluxo RED -> GREEN -> REFACTOR).
- `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md` (factory + seeds + cobertura).

Este guideline define especificamente a separacao de responsabilidades do modelo e a obrigatoriedade de migration/factory/seeder por modelo.

## Criterio de conclusao

Nao considerar task concluida sem:
1. Modelo no local correto.
2. Migration + Factory + Seeder presentes.
3. Regras de testes aplicadas conforme guidelines oficiais citadas acima.

=== .ai/otimizacao-solicitacoes rules ===

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

=== .ai/padrao-botoes-acao-tabelas-e-icones rules ===

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

=== .ai/padroes rules ===

# Padroes de Implementacao

## Regra Geral

Toda feature de dominio nova deve nascer em modulo (`modules/Ajustatech/<Modulo>/src`) e nao em `app/`.

## Fluxo preferencial para novo modulo

1. Executar `php artisan make:module NomeModulo`.
2. Revisar instrucoes exibidas pelo comando.
3. Registrar provider do modulo no `CoreServiceProvider` (antes do `ViewServiceProvider`).
4. Adicionar namespace PSR-4 no `composer.json` raiz.
5. Rodar `composer dumpautoload`.
6. Garantir diretivas de teste no `phpunit.xml` para o modulo.

## Estrutura minima esperada por modulo

- `Commands/`
- `Database/Factories`
- `Database/Migrations`
- `Database/Models`
- `Database/Seeders`
- `Lang/en` e `Lang/pt-BR`
- `Livewire/`
- `Menu/`
- `Providers/`
- `Routes/`
- `Tests/Feature` e `Tests/Unit`
- `Views/livewire`
- `composer.json`

## Convencoes Livewire

- Componentes com `#[Layout('core::layouts.app')]`.
- Par de componentes por entidade:
  - `Show<Entidade>`
  - `<Entidade>Management`
- Em fluxos com modal sobre modal, manter o modal anterior aberto e exibir o novo na frente (stack), salvo quando houver requisito explicito para fechar o anterior.
- Todo formulario/modal de entrada deve retornar aos valores padrao quando for fechado sem salvar/atualizar (incluindo fechar por `X`, botao cancelar, ESC e clique no backdrop).
- Rotas tipicas:
  - listagem/show
  - cadastro
  - edicao
- Para telas de listagem em tabela, seguir:
  - `/.ai/guidelines/projeto/filtros-tabelas-modulo.md`.

## Convencoes de arquitetura por funcionalidade (obrigatorio)

- Quando houver mais de uma funcionalidade no mesmo modulo, separar por pasta de funcionalidade dentro das pastas padrao do modulo.
- Todo modulo deve possuir pasta `Services/`.
- Todo service deve possuir interface correspondente.
- Services devem ser resolvidos por interface via bind no provider do modulo (metodo `register()`).
- Toda feature que usa armazenamento deve gravar em pasta propria por modulo/funcionalidade no disco local e/ou remoto (evitar pastas genericas compartilhadas).
- Ao salvar upload, remover/limpar arquivos temporarios usados no processo para evitar lixo acumulado.
- Priorizar `early return` para simplificar fluxo e evitar blocos `else` desnecessarios.
- Nao executar chamadas ao banco dentro de loops; buscar os dados primeiro e depois iterar em memoria.
- Consultas de dados devem ficar nos Models (`Database/Models`); Services devem apenas orquestrar fluxo.
- Em exibicao, carregamento, edicao e exclusao, otimizar sempre a quantidade de consultas ao banco.
- Nao concentrar implementacao completa de um recurso em um unico arquivo quando houver responsabilidades distintas; dividir em arquivos/classes por responsabilidade.
- Fonte de verdade:
  - `/.ai/guidelines/projeto/servicos-e-funcionalidades-modulares.md`.

## Convencoes de modelos e cobertura (obrigatorio)

- Modelos do modulo devem ficar em `Database/Models`.
- Nao colocar codigo de modelo em componentes, services, controllers ou arquivos nao responsaveis por modelo.
- Todo modelo deve possuir migration, factory e seeder.
- Factories e seeders devem respeitar os relacionamentos do dominio e preencher dados relacionais corretamente.
- Seeds e testes devem cobrir todas as funcionalidades do modulo e submodulos (features da funcionalidade), nao apenas fluxo parcial.
- Fonte de verdade:
  - `/.ai/guidelines/projeto/modelos-separacao-e-cobertura.md`.
  - `/.ai/guidelines/projeto/testes-feature-tdd.md`.
  - `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md`.

## Convencoes de comandos internos

- Seeds modulares: `module:seed-*`.
- Orquestracao de seeds:
  - `php artisan module:seed` (executa todos `module:seed-*`).
  - `php artisan dev:seed` (delegando para `module:seed`).
- Migracao de desenvolvimento:
  - `php artisan dev:migrate` (atual: `migrate:fresh`).
- Limpeza de cache de desenvolvimento:
  - `php artisan dev:clear`.
- Comando recomendado para gerar modulo completo:
  - `php artisan make:module <NomeModulo>`.
- Comandos `make:module-*` existem e sao validos, mas sao internos de scaffolding na maior parte dos fluxos.
- Fonte de verdade dos comandos do Core:
  - `/.ai/guidelines/projeto/comandos-core.md`.
- Convencao de seeds obrigatorios:
  - `/.ai/guidelines/projeto/seeds-obrigatorios.md`.
- Convencao profissional de migrations:
  - `/.ai/guidelines/projeto/migrations-laravel-profissional.md`.

## Convencoes de migrations (obrigatorio)

- Seguir `/.ai/guidelines/projeto/migrations-laravel-profissional.md`.
- Em MySQL, sempre considerar limite de 64 caracteres para nome de indice/constraint.
- Para indices compostos e FKs com nomes longos, definir nome explicito curto e descritivo.

## Convencoes de menu

- Cada item deve ter `slug` que corresponda a nome de rota.
- Quando rota existe, `MenuRouteResolver` substitui URL final com base no `slug`.
- Evitar URL hardcoded quando houver rota nomeada.

## Convencoes de i18n

- Modulos devem concentrar textos em `Lang/<locale>/messages.php` e arquivos correlatos.
- Priorizar `trans('modulo::arquivo.chave')` em componentes e views.

## Convencoes de validacao

- Validacoes reutilizaveis devem ser centralizadas em:
  - `modules/Ajustatech/Core/src/Rules`
- Quando nao existir regra pronta, criar no Core e reutilizar no modulo que precisar.
- Toda feature nova deve aplicar sanitizacao + validacao backend e restricoes de frontend quando houver input.
- Toda feature nova tambem deve passar pelo gate obrigatorio de verificacao antes de ser considerada pronta.
- Fonte de verdade:
  - `/.ai/guidelines/projeto/validacoes-core-reutilizaveis.md`.
  - `/.ai/guidelines/projeto/validacoes-sanitizacao-seguranca.md`.
  - `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`.

## Convencoes de testes (obrigatorio)

- Fluxo TDD para feature: RED -> GREEN -> REFACTOR.
- Antes de implementar, criar teste de **Feature** e comprovar falha inicial.
- Implementar somente depois da falha validada.
- Concluir apenas com teste de Feature passando.
- Toda implementacao nova deve ter revisao minima de testes validos, invalidos, seguranca e performance quando aplicavel.
- Apos executar testes, rodar obrigatoriamente `php artisan dev:reinstall` para reconstruir e preencher o banco local.
- Fonte de verdade para esse fluxo:
  - `/.ai/guidelines/projeto/testes-feature-tdd.md`.
  - `/.ai/guidelines/projeto/pos-testes-dev-reinstall.md`.
  - `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`.
- Factories + Seeds + Testes:
  - `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md`.

## Convencoes de responsividade (obrigatorio)

- Pensar primeiro na visualizacao em dispositivo movel (mobile-first).
- Depois ajustar e validar a experiencia completa para desktop/PC.
- Toda tela, componente e fluxo novo deve funcionar nos dois modos de exibicao: movel e desktop.
- Nao considerar implementacao concluida sem verificacao visual e funcional em ambos os contextos.

## Convencoes de performance de solicitacoes (obrigatorio)

- Toda funcionalidade nova deve otimizar quantidade de solicitacoes HTTP/Livewire.
- Fluxos visuais (ajuda, abrir modal, preview) devem evitar request ao backend quando os dados ja estiverem disponiveis.
- Acoes de negocio (salvar, editar, excluir) devem buscar fluxo de 1 solicitacao efetiva por acao confirmada.
- Sempre executar testes de requisicoes/performance dos fluxos principais (exibir, carregar, editar e excluir), registrando a contagem de requests/queries e validando reducao de desperdicio.
- Fonte de verdade:
  - `/.ai/guidelines/projeto/otimizacao-solicitacoes.md`.
  - `/.ai/guidelines/projeto/verificacao-obrigatoria-codigo-novo.md`.

## Referencias Vuexy (somente referencia)

- Use `templete/Vuexy/resources` para copiar padrao visual e blocos Blade/SCSS/JS.
- Qualquer adaptacao deve ser feita no codigo real do projeto.
- Nao tratar arquivos de `templete/` como fonte de verdade de negocio.

## Convencoes de qualidade profissional (obrigatorio)

- Seguir skill dedicada: `/.ai/skills/qualidade-profissional/SKILL.md`.
- Esta skill e a fonte unica para padroes de codigo profissional, Clean Code, SOLID, mentalidade agil, simplicidade e prevencao de N+1.

=== .ai/pos-testes-dev-reinstall rules ===

# Pos-Testes Obrigatorio: `dev:reinstall`

## Objetivo

Garantir que, apos a execucao dos testes, o ambiente de desenvolvimento volte para um estado consistente com banco reconstruido e dados preenchidos.

## Regra obrigatoria

Ao finalizar qualquer implementacao com execucao de testes, sempre rodar:

```bash
php artisan dev:reinstall
```

## O que o comando deve garantir

- limpeza do banco atual;
- execucao de migrations;
- limpeza de caches de desenvolvimento;
- execucao de seeds para deixar o sistema preenchido.

## Quando aplicar

- Depois de `php artisan test` parcial ou completo.
- Depois de alterar migrations, models, factories, seeders ou fluxos dependentes de dados.
- Antes de considerar a tarefa concluida para entrega local.

## Checklist de conclusao

1. Testes executados e resultado registrado.
2. `php artisan dev:reinstall` executado com sucesso.
3. Banco reconstruido e dados seedados disponiveis para validacao manual.
4. Sem erro pendente de migration/seeder apos reinstall.

=== .ai/referencias rules ===

# Referencias Externas

## Fontes consultadas

- Laravel Boost: https://laravel.com/ai/boost
- Vuexy Documentation: https://demos.pixinvent.com/vuexy-html-admin-template/documentation/

## Nota importante

Essas fontes orientam padrao e fluxo, mas a implementacao deste repositorio segue a arquitetura modular propria em `modules/Ajustatech/*`.

## Sobre a pasta de template

`templete/Vuexy/resources` e um espelho de referencia para UI.
- Serve para consulta de estrutura, componentes e estilos.
- Nao substitui o codigo real da aplicacao em `resources/*` e `modules/*`.

=== .ai/seeds-em-lote-performance rules ===

# Seeds em Lote (Performance)

## Objetivo

Padronizar seeders para inserir muitos registros com o menor numero de queries possivel.

## Regra obrigatoria

- Quando um seeder inserir varios registros, preferir insercao em lote (`insert`) em vez de loop com `create`/`updateOrCreate`.
- A meta padrao e 1 query de escrita por conjunto de registros do mesmo recurso.

## Padrao recomendado

1. Montar array final com todos os registros.
2. Adicionar `id`, `created_at` e `updated_at` no proprio array.
3. Executar `Model::query()->insert($rows)`.

## Quando usar `upsert`

- Usar `upsert` somente quando houver necessidade real de atualizar registros existentes.
- Se usar `upsert`, garantir chave unica/indice adequado para evitar comportamento inesperado.

## Anti-padroes

- Loop com `create()` para dezenas/centenas de registros.
- Loop com `updateOrCreate()` quando ambiente e reconstruido por `dev:reinstall`.
- Multiplicar queries sem necessidade em seeders de carga base.

## Checklist de conclusao

1. Seeder de volume esta em lote?
2. IDs e timestamps foram definidos corretamente?
3. Dados continuam reais/coerentes com a funcionalidade?
4. Seeder funciona apos `php artisan dev:reinstall`?

=== .ai/seeds-obrigatorios rules ===

# Seeds Obrigatorios para Recursos e Modulos

## Regra principal

Ao criar um recurso novo ou um modulo novo, sempre criar seeds com o maximo de cenarios possiveis para povoar o banco.

## Regra de nomenclatura e realismo dos dados (obrigatoria)

- Todo seed deve usar nomes e valores reais/coerentes com a funcionalidade pedida.
- Evitar dados genericos sem contexto como `Teste`, `Item 1`, `Example`, `Foo`, `Bar`.
- Os registros seedados devem refletir linguagem de negocio do dominio (servicos, categorias, tipos, status e descricoes reais).
- Quando houver valores monetarios, percentuais, prazos ou textos de apoio, usar faixas e conteudos plausiveis para o cenario.
- Factories usadas por seeds devem seguir a mesma regra de realismo.

Exemplos:
- Bom: `Troca de tela`, `Limpeza interna`, `Diagnostico eletrico`.
- Ruim: `Servico 1`, `Procedimento teste`, `Nome exemplo`.

## Objetivo

- Garantir base de dados rica para desenvolvimento e testes.
- Facilitar validacao de fluxos reais da aplicacao.
- Reduzir dados manuais e ambientes vazios.

## O que deve ser seedado

- Variacoes principais do dominio (status, tipos, categorias, perfis, etc.).
- Casos comuns de uso.
- Casos de borda relevantes para o recurso.
- Relacionamentos entre entidades, quando existirem.
- Relacionamentos devem ser preenchidos de forma correta e consistente (FKs validas, cardinalidade esperada e coerencia de negocio).
- Cobertura completa das funcionalidades do modulo e submodulos (features), incluindo fluxos relacionais entre elas.

## Regras por modulo

- Cada modulo deve ter seus seeders em `Database/Seeders`.
- Cada modulo deve expor comando `module:seed-*`.
- O comando global `module:seed` deve conseguir executar todos os seeds de modulos.
- Para seeders com multiplos registros, seguir performance em lote:
  - `/.ai/guidelines/projeto/seeds-em-lote-performance.md`.

## Execucao obrigatoria apos criar/alterar recurso

Sempre rodar:
1. `php artisan dev:migrate`
2. `php artisan dev:seed`

Ou, quando necessario:
1. `php artisan module:seed`

## Criterio de pronto

Uma feature/modulo so e considerada pronta quando:
- seeds cobrindo cenarios relevantes foram criados/atualizados;
- seeds cobrem dados relacionais corretamente;
- seeds contemplam todas as funcionalidades do modulo/submodulo;
- o povoamento global dos modulos foi executado com sucesso.

=== .ai/seguranca rules ===

# Seguranca e Integridade

## Escopo

Diretrizes de seguranca para Laravel + Livewire no contexto modular Ajustatech.

## Validacao

- Validar entradas no componente Livewire e/ou Form Request antes de persistir.
- Reutilizar regras do Core quando aplicavel (`CpfValidator`, `CnpjValidation`).
- Em edicao, usar `Rule::unique(...)->ignore($id)` para evitar falso positivo de unicidade.

## Mass Assignment

- Garantir `fillable` consistente em todos os Models modulares.
- Nunca usar `guarded = []` sem justificativa formal.

## Integridade transacional

- Operacoes financeiras devem usar transacao quando houver mais de uma escrita relacionada.
- Manter consistencia entre transacoes e saldos (ex.: cash + balances + transactions).

## Eventos de UI sensiveis

- Confirmacoes de acao devem passar por `SwitchAlertDispatch` quando houver impacto em dados.
- Evitar executar alteracoes destrutivas sem confirmacao.

## Rotas e autorizacao

- Cada rota de modulo deve ser nomeada e preparada para middleware/policies.
- Nao assumir que tela Livewire ja implica autorizacao.

## Seed e ambiente

- Seeds modulares (`module:seed-*`) devem ser idempotentes quando possivel.
- Evitar dados sensiveis hardcoded.

## Referencia Vuexy

`templete/Vuexy/resources` e apenas referencia de layout.
- Nao inserir regras de negocio ali.
- Nao usar pasta de referencia como origem de assets sensiveis de producao.

## Pontos de atencao atuais do projeto

- Validar encoding UTF-8 em textos PT-BR para evitar caracteres corrompidos.
- Revisar consistencia de nomes de pasta `menu` vs `Menu` entre modulos para compatibilidade cross-platform.

=== .ai/servicos-e-funcionalidades-modulares rules ===

# Servicos e Funcionalidades Modulares

## Objetivo

Padronizar a organizacao interna dos modulos quando houver mais de uma funcionalidade de negocio, facilitando manutencao, escalabilidade e testes.

## Modelo de composicao (Modulo -> Funcionalidades)

Padrao recomendado: manter um modulo pai e criar funcionalidades (submodulos de recurso) dentro dele.

Exemplo real de referencia:
- Modulo pai: `ServiceOrder`
- Funcionalidade/recurso: `Procedure`

Fluxo de registro:
1. O provider do modulo pai registra o provider da funcionalidade no `register()`.
2. O provider da funcionalidade carrega rotas, migrations, bindings, comandos e componentes da propria funcionalidade.
3. O provider do modulo pai continua responsavel pelos itens centrais do modulo (menus, rotas base, views base, comandos de modulo).

## Regra 1: Separacao por funcionalidade

Quando um modulo tiver duas ou mais funcionalidades, separar por pasta de funcionalidade dentro das pastas padrao do modulo.

Exemplos esperados:
- `Livewire/<Funcionalidade>/...`
- `Views/livewire/<funcionalidade-kebab-case>/...`
- `Tests/Feature/Livewire/<Funcionalidade>/...`
- `Tests/Feature/Database/<Funcionalidade>/...` (quando aplicavel)
- `Database/Migrations/<Funcionalidade>/...` (quando aplicavel)
- `Database/Seeders/<Funcionalidade>/...` (quando aplicavel)
- `Database/Factories/<Funcionalidade>/...` (quando aplicavel)
- `Providers/<Funcionalidade>/<Funcionalidade>ServiceProvider.php` (quando aplicavel)
- `Commands/<Funcionalidade>/...` (quando aplicavel)
- `storage/<disco>/<modulo>/<funcionalidade>/...` para arquivos de upload (quando aplicavel), incluindo disco local e remoto.

Regra de armazenamento:
- Cada modulo/funcionalidade com upload deve ter pasta propria de armazenamento no disco local e/ou remoto utilizado.
- Nao usar pasta raiz compartilhada entre funcionalidades diferentes.

## Regra 1.1: Tudo da funcionalidade dentro da pasta da funcionalidade

Para recurso novo, concentrar artefatos da feature na propria pasta de funcionalidade dentro do modulo:
- migration da feature em `Database/Migrations/<Funcionalidade>/`
- factory da feature em `Database/Factories/<Funcionalidade>/`
- seeder da feature em `Database/Seeders/<Funcionalidade>/`
- comandos da feature em `Commands/<Funcionalidade>/`
- provider da feature em `Providers/<Funcionalidade>/`

Depois, registrar no provider do modulo pai.

## Regra 2: Pasta Services obrigatoria

Todo modulo deve possuir a pasta:
- `modules/Ajustatech/<Modulo>/src/Services`

## Regra 3: Todo service deve ter interface

Cada service concreto deve possuir sua interface correspondente.

Padrao recomendado:
- Interface em `Services/<Funcionalidade>/Contracts/<NomeService>Interface.php`
- Implementacao em `Services/<Funcionalidade>/<NomeService>.php`
- Regra obrigatoria para funcionalidades novas: contrato deve ficar dentro da pasta da funcionalidade e nao em `Services/Contracts` global.

Alternativa aceita em modulo legado:
- Interface e implementacao na mesma pasta de `Services`, mantendo nomenclatura clara.

## Regra 3.1: Consultas no Model, nao no Service

- Toda consulta de banco (listagem, busca por ID, filtros e leituras relacionadas) deve ser encapsulada em Model (`Database/Models`), via scopes ou metodos de dominio.
- Services devem atuar como orquestradores de caso de uso, sem concentrar query SQL/Eloquent de leitura.
- Nao duplicar regra de consulta em Service, Livewire ou Controller quando ela pertencer ao dominio do modelo.

## Regra 4: Resolucao via container (bind)

Services devem ser consumidos por interface e resolvidos via container do Laravel.
Nao injetar implementacao concreta diretamente em controllers, Livewire ou jobs.

Registrar bindings no provider do modulo:
- `modules/Ajustatech/<Modulo>/src/Providers/<Modulo>ServiceProvider.php`
- Metodo `register()`

Exemplo:
```php
public function register(): void
{
    $this->app->bind(
        OrderWorkflowServiceInterface::class,
        OrderWorkflowService::class
    );
}
```

## Regra 4.1: Providers de funcionalidade

Provider da funcionalidade deve:
1. Fazer binds da funcionalidade no `register()`.
2. Registrar configuracoes de diretorios da funcionalidade (ex.: midia) no `register()`.
3. Carregar `Routes/<funcionalidade>.php` e `Database/Migrations/<Funcionalidade>` no `boot()`.
4. Registrar comandos da funcionalidade no `boot()`.
5. Registrar componentes Livewire da funcionalidade no `boot()`.

## Regra 4.2: Comandos em cadeia (manual)

Em comandos de modulo que orquestram features:
- Chamar comandos da feature explicitamente com `$this->call('feature:...')`.
- Evitar auto-descoberta dinamica de comandos via loop em `Artisan::all()` para fluxo principal.
- Em seeds de modulo, chamar explicitamente o seeder do modulo e depois os comandos de seed das features.

## Regra 5: Convencao de nomes

- Interface: sufixo `Interface`
- Implementacao: sufixo `Service`
- Nome por contexto de negocio, evitando nomes genericos como `MainService`.

## Regra 6: Otimizacao obrigatoria de consultas e limpeza de temporarios

- Em exibicao, carregamento, edicao e exclusao, reduzir ao maximo a quantidade de queries.
- Evitar N+1 com carregamento apropriado de relacoes e consultas em lote.
- Nao executar query dentro de loop.
- Em fluxo com upload, ao salvar definitivamente o arquivo, limpar/remover temporarios do processo para evitar acumulo de lixo em storage temporario.
- Cobrir os fluxos com testes de requisicoes/performance para validar que as otimizacoes foram aplicadas.

## Validacao final antes de concluir task

1. Existe separacao por funcionalidade nas pastas padrao?
2. O modulo possui `Services/`?
3. Todo service novo tem interface?
4. Binding interface -> implementacao foi registrado no provider?
5. Consumo em codigo esta por interface (DI), nao por classe concreta?
6. Provider do modulo pai registra providers das funcionalidades?
7. Cada funcionalidade registra seus proprios comandos/migrations/rotas no provider proprio?

=== .ai/testes-feature-tdd rules ===

# Testes de Feature (TDD Obrigatorio)

## Regra principal

Antes de implementar qualquer feature:
1. Criar o teste de **Feature** primeiro.
2. Executar o teste e confirmar que ele **falha** (RED).
3. Implementar a feature (GREEN).
4. Executar novamente e confirmar que o teste **passa**.
5. Refatorar mantendo todos os testes verdes (REFACTOR).

## Escopo

- O tipo de teste padrao para novas features deve ser **Feature Test**.
- Para modulos, priorizar:
  - `modules/Ajustatech/<Modulo>/src/Tests/Feature/*`
- Para app base (quando nao modular):
  - `tests/Feature/*`

## Padrao de fluxo (Red-Green-Refactor)

- RED:
  - escrever o cenario esperado da feature;
  - rodar o teste alvo isolado e validar falha.
- GREEN:
  - implementar o minimo necessario para passar.
- REFACTOR:
  - limpar codigo sem alterar comportamento;
  - rodar suite relevante novamente.

## Regras de qualidade

- Cada teste deve validar comportamento observavel da feature.
- Evitar teste acoplado a detalhes internos de implementacao.
- Em telas Livewire, validar renderizacao, acao e resultado esperado.
- Em persistencia, validar banco com asserts de database.

## Comandos uteis

- Rodar todos os testes:
  - `php artisan test`
- Rodar somente testes de Feature:
  - `php artisan test --testsuite=Feature`
- Rodar arquivo especifico:
  - `php artisan test modules/Ajustatech/<Modulo>/src/Tests/Feature/<Arquivo>Test.php`

## Criterio de pronto

Uma feature so e considerada pronta quando:
- existe teste de Feature criado antes da implementacao;
- foi comprovada falha inicial (RED);
- passou apos implementacao (GREEN);
- nao houve regressao nos testes relacionados.

=== .ai/traits-core-para-recursos-existentes rules ===

# Traits no Core para Recursos Existentes

## Regra principal

Sempre que for necessario implementar um recurso em algo que ja existe, criar uma **Trait** e colocar em:

- `modules/Ajustatech/Core/src/Traits`

## Objetivo

Evitar duplicacao de logica em classes existentes e manter extensoes reutilizaveis entre modulos.

## Quando aplicar

- Quando uma classe existente precisar ganhar comportamento novo sem inflar a propria classe.
- Quando a mesma logica puder ser reutilizada em mais de um modulo/componente/model.
- Quando o recurso for transversal (cross-cutting) no projeto.

## Como aplicar

1. Criar a trait no Core (`Core/src/Traits`).
2. Dar nome claro orientado ao comportamento.
3. Manter metodos coesos e focados no recurso.
4. Importar a trait (`use`) na classe do modulo que precisa dela.
5. Cobrir com testes de Feature (e Unit quando fizer sentido).

## Exemplo real no projeto

- `SwitchAlertDispatch` em `modules/Ajustatech/Core/src/Traits`
- Consumida em componentes Livewire de modulos como `Customer`.

## Beneficios

- Reuso padronizado.
- Menos acoplamento e menos codigo repetido.
- Evolucao centralizada de comportamento compartilhado.

=== .ai/validacoes-core-reutilizaveis rules ===

# Validacoes Reutilizaveis no Core

## Regra principal

Quando nao existir validacao para um campo, a validacao deve ser criada em:

- `modules/Ajustatech/Core/src/Rules`

## Objetivo

Centralizar regras de validacao reutilizaveis para evitar duplicacao entre modulos e manter consistencia de comportamento.

## Como aplicar

1. Verificar se a regra ja existe em `Core/src/Rules`.
2. Se nao existir, criar uma nova classe de regra em `Core/src/Rules`.
3. Implementar a regra seguindo o contrato de validacao do Laravel (`ValidationRule`).
4. No modulo que precisar da validacao, importar e usar a regra no `validate()`/Form Request.

## Uso no modulo

- A regra criada no Core deve ser chamada explicitamente no modulo consumidor.
- Exemplo de import em modulo:
  - `use Ajustatech\Core\Rules\NomeDaRegra;`
- Exemplo de uso:
  - `'campo' => ['required', new NomeDaRegra]`

## Beneficios

- Reuso entre modulos.
- Menos codigo duplicado.
- Padrao unico de validacao para campos equivalentes.
- Manutencao mais simples.

## Referencia pratica no projeto

Ja existem regras compartilhadas no Core, como:
- `CpfValidator`
- `CnpjValidation`

Essas regras sao consumidas por modulos como `Customer`.

=== .ai/validacoes-sanitizacao-seguranca rules ===

# Validacoes, Sanitizacao e Seguranca de Entrada

## Objetivo

Padronizar tratamento de entrada de dados em backend e frontend para reduzir risco de dados invalidos, inconsistentes ou inseguros.

## Regra obrigatoria para toda feature nova

Toda entrada do usuario deve passar por:
1. Sanitizacao.
2. Validacao.
3. Persistencia somente de dados aprovados.

## Backend (obrigatorio)

- Sanitizar antes de validar e salvar:
  - `trim` em strings;
  - normalizacao de espacos;
  - remocao de tags HTML quando o campo nao for rich text;
  - limite de tamanho defensivo.
- Validar com regras explicitas por campo (`required`, `nullable`, `max`, `numeric`, `url`, `in`, etc.).
- Regras condicionais devem refletir regras de negocio (ex.: chave ligada exige pelo menos um campo).
- Nunca confiar em restricao apenas de frontend.

## Frontend (obrigatorio quando houver input)

- Aplicar restricoes HTML minimas:
  - `maxlength`, `min`, `max`, `step`, `inputmode`, `type` adequado.
- Em JS puro (sem Livewire para aquele input), sanitizar no cliente antes de enviar.
- Mensagens de erro devem ser claras e alinhadas ao campo.
- Toda mensagem de validacao deve ser renderizada imediatamente abaixo do campo correspondente (evitar bloco de erros global no topo/rodape do formulario).

## Seguranca

- Evitar persistir HTML bruto quando nao necessario.
- Validar e restringir URLs a formato valido.
- Evitar interpolacao insegura em scripts/atributos; usar helpers de escape/serializacao.
- Nao executar conteudo de entrada do usuario no cliente.

## Uploads e arquivos temporarios (obrigatorio)

- Todo fluxo de upload deve prever limpeza de temporarios apos salvar os arquivos definitivos.
- Nao manter arquivo temporario sem necessidade depois da persistencia.
- Validar periodicamente se nao ha acumulo de lixo em storage temporario da feature.

## Checklist de conclusao

1. Todo campo recebeu sanitizacao no backend?
2. Todo campo recebeu validacao no backend?
3. Campos de frontend possuem restricoes basicas?
4. Regras condicionais de negocio estao cobertas?
5. Testes cobrem casos validos e invalidos principais?
6. Fluxos de upload limpam os temporarios apos salvar?

=== .ai/verificacao-obrigatoria-codigo-novo rules ===

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

=== .ai/vuexy-recursos-catalogo rules ===

﻿# Catalogo Completo: templete/Vuexy/resources


> Aviso: esta pasta e somente referencia. Nao use como fonte de verdade de negocio.

Total de arquivos lidos: **538**


## Resumo por Categoria


- assets/css: 1 arquivo(s)
- assets/js: 111 arquivo(s)
- assets/vendor/fonts: 3 arquivo(s)
- assets/vendor/js: 8 arquivo(s)
- assets/vendor/libs/@algolia: 1 arquivo(s)
- assets/vendor/libs/@form-validation: 4 arquivo(s)
- assets/vendor/libs/_tabler: 1 arquivo(s)
- assets/vendor/libs/animate-css: 1 arquivo(s)
- assets/vendor/libs/animate-on-scroll: 2 arquivo(s)
- assets/vendor/libs/apex-charts: 2 arquivo(s)
- assets/vendor/libs/bloodhound: 1 arquivo(s)
- assets/vendor/libs/bootstrap-daterangepicker: 2 arquivo(s)
- assets/vendor/libs/bootstrap-select: 2 arquivo(s)
- assets/vendor/libs/bs-stepper: 2 arquivo(s)
- assets/vendor/libs/chartjs: 2 arquivo(s)
- assets/vendor/libs/cleave-zen: 1 arquivo(s)
- assets/vendor/libs/clipboard: 1 arquivo(s)
- assets/vendor/libs/datatables-bs5: 2 arquivo(s)
- assets/vendor/libs/datatables-buttons-bs5: 1 arquivo(s)
- assets/vendor/libs/datatables-fixedcolumns-bs5: 1 arquivo(s)
- assets/vendor/libs/datatables-fixedheader-bs5: 1 arquivo(s)
- assets/vendor/libs/datatables-responsive-bs5: 1 arquivo(s)
- assets/vendor/libs/datatables-rowgroup-bs5: 1 arquivo(s)
- assets/vendor/libs/datatables-select-bs5: 1 arquivo(s)
- assets/vendor/libs/dropzone: 2 arquivo(s)
- assets/vendor/libs/flatpickr: 2 arquivo(s)
- assets/vendor/libs/fullcalendar: 2 arquivo(s)
- assets/vendor/libs/hammer: 1 arquivo(s)
- assets/vendor/libs/highlight: 3 arquivo(s)
- assets/vendor/libs/jkanban: 2 arquivo(s)
- assets/vendor/libs/jquery: 1 arquivo(s)
- assets/vendor/libs/jquery-idletimer: 1 arquivo(s)
- assets/vendor/libs/jquery-repeater: 1 arquivo(s)
- assets/vendor/libs/jquery-timepicker: 2 arquivo(s)
- assets/vendor/libs/jstree: 9 arquivo(s)
- assets/vendor/libs/leaflet: 7 arquivo(s)
- assets/vendor/libs/mapbox-gl: 2 arquivo(s)
- assets/vendor/libs/masonry: 1 arquivo(s)
- assets/vendor/libs/maxLength: 1 arquivo(s)
- assets/vendor/libs/moment: 1 arquivo(s)
- assets/vendor/libs/node-waves: 2 arquivo(s)
- assets/vendor/libs/notiflix: 2 arquivo(s)
- assets/vendor/libs/notyf: 2 arquivo(s)
- assets/vendor/libs/nouislider: 2 arquivo(s)
- assets/vendor/libs/numeral: 1 arquivo(s)
- assets/vendor/libs/perfect-scrollbar: 2 arquivo(s)
- assets/vendor/libs/pickr: 5 arquivo(s)
- assets/vendor/libs/plyr: 2 arquivo(s)
- assets/vendor/libs/popper: 1 arquivo(s)
- assets/vendor/libs/quill: 6 arquivo(s)
- assets/vendor/libs/raty-js: 2 arquivo(s)
- assets/vendor/libs/select2: 2 arquivo(s)
- assets/vendor/libs/shepherd: 2 arquivo(s)
- assets/vendor/libs/sortablejs: 1 arquivo(s)
- assets/vendor/libs/spinkit: 1 arquivo(s)
- assets/vendor/libs/sweetalert2: 2 arquivo(s)
- assets/vendor/libs/swiper: 2 arquivo(s)
- assets/vendor/libs/tagify: 5 arquivo(s)
- assets/vendor/libs/typeahead-js: 2 arquivo(s)
- assets/vendor/scss: 105 arquivo(s)
- css: 1 arquivo(s)
- js: 3 arquivo(s)
- menu: 2 arquivo(s)
- views/_partials: 20 arquivo(s)
- views/content: 152 arquivo(s)
- views/layouts: 20 arquivo(s)


## Categoria: assets/css


- `assets/css/demo.css` (2.53 KB): Quando precisar de estilos globais demonstrativos do template.


## Categoria: assets/js


- `assets/js/app-academy-course.js` (0.54 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-academy-course-details.js` (0.66 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-academy-dashboard.js` (16.79 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-access-permission.js` (8.13 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-access-roles.js` (22.77 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-calendar.js` (19.26 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-calendar-events.js` (2.74 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-chat.js` (7.53 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-category-list.js` (10.62 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-customer-all.js` (21.27 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-customer-detail.js` (2.8 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-customer-detail-overview.js` (7.22 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-dashboard.js` (30.59 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-order-details.js` (8.07 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-order-list.js` (17.8 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-product-add.js` (3.71 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-product-list.js` (26.68 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-referral.js` (18.49 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-reviews.js` (26.68 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-ecommerce-settings.js` (1 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-email.js` (13.69 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-invoice-add.js` (3.8 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-invoice-edit.js` (3.88 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-invoice-list.js` (16.65 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-invoice-print.js` (0.08 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-kanban.js` (15.46 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-logistics-dashboard.js` (14.18 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-logistics-fleet.js` (3.57 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-user-list.js` (26.79 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-user-view.js` (2.78 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-user-view-account.js` (21.63 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-user-view-billing.js` (1.8 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/app-user-view-security.js` (2.33 KB): Quando precisar de comportamento JS especifico de paginas app do Vuexy (widgets, filtros, datatables).
- `assets/js/cards-actions.js` (5.19 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/cards-advance.js` (4.65 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/cards-analytics.js` (33.73 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/cards-statistics.js` (33.92 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/charts-apex.js` (25.99 KB): Quando precisar de referencia para graficos com Apex Charts ou ChartJS.
- `assets/js/charts-chartjs.js` (27.36 KB): Quando precisar de referencia para graficos com Apex Charts ou ChartJS.
- `assets/js/charts-chartjs-legend.js` (1.52 KB): Quando precisar de referencia para graficos com Apex Charts ou ChartJS.
- `assets/js/config.js` (2.08 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/dashboards-analytics.js` (21.54 KB): Quando precisar de scripts de dashboards com cards, metricas e graficos.
- `assets/js/dashboards-crm.js` (20.26 KB): Quando precisar de scripts de dashboards com cards, metricas e graficos.
- `assets/js/extended-ui-blockui.js` (31.08 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-drag-and-drop.js` (2.17 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-media-player.js` (0.17 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-misc-clipboardjs.js` (0.69 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-misc-idle-timer.js` (6.25 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-misc-numeraljs.js` (0.95 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-perfect-scrollbar.js` (1.06 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-star-ratings.js` (10.84 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-sweetalert2.js` (15.12 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-timeline.js` (0.22 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-tour.js` (4.27 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/extended-ui-treeview.js` (9.49 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/form-basic-inputs.js` (0.18 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-input-group.js` (1.04 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-layouts.js` (3.25 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-editors.js` (1.51 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-extras.js` (5.89 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-file-upload.js` (1.59 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-pickers.js` (8.34 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-selects.js` (1.23 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-sliders.js` (7.65 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-tagify.js` (7.15 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/forms-typeahead.js` (7.49 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-validation.js` (10.39 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-wizard-icons.js` (5.69 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-wizard-numbered.js` (5.33 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/form-wizard-validation.js` (8.43 KB): Quando precisar de comportamento de formularios (validacao, wizard, selects, pickers e uploads).
- `assets/js/front-config.js` (1.78 KB): Quando precisar de comportamento JS para paginas front ou landing.
- `assets/js/front-main.js` (4.79 KB): Quando precisar de comportamento JS para paginas front ou landing.
- `assets/js/front-page-landing.js` (4.38 KB): Quando precisar de comportamento JS para paginas front ou landing.
- `assets/js/front-page-payment.js` (2.4 KB): Quando precisar de comportamento JS para paginas front ou landing.
- `assets/js/front-page-pricing.js` (1.06 KB): Quando precisar de comportamento JS para paginas front ou landing.
- `assets/js/main.js` (25.25 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/maps-leaflet.js` (161.12 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-add-new-address.js` (2.08 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-add-new-cc.js` (3.28 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-add-permission.js` (1.01 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-add-role.js` (1.28 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-create-app.js` (3.01 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-edit-cc.js` (3.02 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-edit-permission.js` (1.01 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-edit-user.js` (1.53 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-enable-otp.js` (1.81 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-share-project.js` (1.14 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/modal-two-factor-auth.js` (0.69 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/offcanvas-add-payment.js` (0.72 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/offcanvas-send-invoice.js` (0.3 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/pages-account-settings-account.js` (6.28 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-account-settings-billing.js` (6.55 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-account-settings-security.js` (3.78 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-auth.js` (3.48 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-auth-multisteps.js` (10.49 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-auth-two-steps.js` (2.76 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-pricing.js` (0.98 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/pages-profile.js` (8.37 KB): Quando precisar de scripts para paginas de perfil, configuracoes, autenticacao e paginas gerais.
- `assets/js/tables-datatables-advanced.js` (19.45 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/tables-datatables-basic.js` (40.39 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/tables-datatables-extensions.js` (19.83 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/ui-app-brand.js` (1.98 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-carousel.js` (4.81 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-menu.js` (3.05 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-modals.js` (2.48 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-navbar.js` (0.41 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-popover.js` (0.31 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/ui-toasts.js` (6.86 KB): Quando precisar de exemplos JS para componentes de interface (toast, modal, menu, navbar).
- `assets/js/wizard-ex-checkout.js` (3.79 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/wizard-ex-create-deal.js` (7.5 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `assets/js/wizard-ex-property-listing.js` (9.94 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.


## Categoria: assets/vendor/fonts


- `assets/vendor/fonts/flag-icons.scss` (0.11 KB): Quando precisar de fontes, icones e arquivos de apoio visual do tema.
- `assets/vendor/fonts/fontawesome.scss` (0.27 KB): Quando precisar de fontes, icones e arquivos de apoio visual do tema.
- `assets/vendor/fonts/iconify/iconify.css` (2608.87 KB): Quando precisar de fontes, icones e arquivos de apoio visual do tema.


## Categoria: assets/vendor/js


- `assets/vendor/js/_template-customizer/_template-customizer.html` (4.42 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/_template-customizer/_template-customizer.scss` (6.31 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/bootstrap.js` (0.11 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/dropdown-hover.js` (2.16 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/helpers.js` (41.44 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/mega-dropdown.js` (5.66 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/menu.js` (31.13 KB): Quando precisar entender runtime do template (helpers, menu, customizer).
- `assets/vendor/js/template-customizer.js` (61.47 KB): Quando precisar entender runtime do template (helpers, menu, customizer).


## Categoria: assets/vendor/libs/@algolia


- `assets/vendor/libs/@algolia/autocomplete-js.js` (0.22 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/@form-validation


- `assets/vendor/libs/@form-validation/auto-focus.js` (0.15 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/@form-validation/bootstrap5.js` (0.16 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/@form-validation/form-validation.scss` (0.23 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/@form-validation/popular.js` (0.15 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/_tabler


- `assets/vendor/libs/_tabler/_tabler.scss` (6.9 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/animate-css


- `assets/vendor/libs/animate-css/animate.scss` (0.03 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/animate-on-scroll


- `assets/vendor/libs/animate-on-scroll/animate-on-scroll.js` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/animate-on-scroll/animate-on-scroll.scss` (0.02 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/apex-charts


- `assets/vendor/libs/apex-charts/apexcharts.js` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/apex-charts/apex-charts.scss` (2.15 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/bloodhound


- `assets/vendor/libs/bloodhound/bloodhound.js` (0.13 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/bootstrap-daterangepicker


- `assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js` (0.52 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss` (7.51 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/bootstrap-select


- `assets/vendor/libs/bootstrap-select/bootstrap-select.js` (0.05 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/bootstrap-select/bootstrap-select.scss` (4.21 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/bs-stepper


- `assets/vendor/libs/bs-stepper/bs-stepper.js` (1.05 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/bs-stepper/bs-stepper.scss` (9 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/chartjs


- `assets/vendor/libs/chartjs/chartjs.js` (0.1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/chartjs/chartjs.scss` (0.45 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/cleave-zen


- `assets/vendor/libs/cleave-zen/cleave-zen.js` (0.61 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/clipboard


- `assets/vendor/libs/clipboard/clipboard.js` (0.12 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-bs5


- `assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss` (6.58 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js` (1.42 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-buttons-bs5


- `assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss` (0.7 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-fixedcolumns-bs5


- `assets/vendor/libs/datatables-fixedcolumns-bs5/fixedcolumns.bootstrap5.scss` (0.7 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-fixedheader-bs5


- `assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.scss` (0.82 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-responsive-bs5


- `assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss` (1.53 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-rowgroup-bs5


- `assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss` (0.23 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/datatables-select-bs5


- `assets/vendor/libs/datatables-select-bs5/select.bootstrap5.scss` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/dropzone


- `assets/vendor/libs/dropzone/dropzone.js` (1.56 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/dropzone/dropzone.scss` (7.44 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/flatpickr


- `assets/vendor/libs/flatpickr/flatpickr.js` (0.12 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/flatpickr/flatpickr.scss` (12.81 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/fullcalendar


- `assets/vendor/libs/fullcalendar/fullcalendar.js` (0.68 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/fullcalendar/fullcalendar.scss` (9.9 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/hammer


- `assets/vendor/libs/hammer/hammer.js` (0.03 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/highlight


- `assets/vendor/libs/highlight/highlight.js` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/highlight/highlight.scss` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/highlight/highlight-github.scss` (0.04 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jkanban


- `assets/vendor/libs/jkanban/jkanban.js` (0.03 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jkanban/jkanban.scss` (0.08 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jquery


- `assets/vendor/libs/jquery/jquery.js` (0.14 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jquery-idletimer


- `assets/vendor/libs/jquery-idletimer/jquery-idletimer.js` (0.07 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jquery-repeater


- `assets/vendor/libs/jquery-repeater/jquery-repeater.js` (0.04 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jquery-timepicker


- `assets/vendor/libs/jquery-timepicker/jquery-timepicker.js` (0.04 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jquery-timepicker/jquery-timepicker.scss` (1.32 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/jstree


- `assets/vendor/libs/jstree/jstree.js` (0.03 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/jstree.scss` (6.42 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/_theme.scss` (4.19 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default/32px.png` (4.51 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default/40px.png` (1.84 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default/throbber.gif` (1.68 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default-dark/32px.png` (1.53 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default-dark/40px.png` (5.58 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/jstree/themes/default-dark/throbber.gif` (1.68 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/leaflet


- `assets/vendor/libs/leaflet/images/layers.png` (0.68 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/images/layers-2x.png` (1.23 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/images/marker-icon.png` (1.43 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/images/marker-icon-2x.png` (2.41 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/images/marker-shadow.png` (0.6 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/leaflet.js` (0.46 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/leaflet/leaflet.scss` (0.62 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/mapbox-gl


- `assets/vendor/libs/mapbox-gl/mapbox-gl.js` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/mapbox-gl/mapbox-gl.scss` (0.16 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/masonry


- `assets/vendor/libs/masonry/masonry.js` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/maxLength


- `assets/vendor/libs/maxLength/maxLength.scss` (0.81 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/moment


- `assets/vendor/libs/moment/moment.js` (0.1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/node-waves


- `assets/vendor/libs/node-waves/node-waves.js` (0.07 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/node-waves/node-waves.scss` (0.13 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/notiflix


- `assets/vendor/libs/notiflix/notiflix.js` (0.17 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/notiflix/notiflix.scss` (0.27 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/notyf


- `assets/vendor/libs/notyf/notyf.js` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/notyf/notyf.scss` (0.89 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/nouislider


- `assets/vendor/libs/nouislider/nouislider.js` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/nouislider/nouislider.scss` (6.82 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/numeral


- `assets/vendor/libs/numeral/numeral.js` (0.12 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/perfect-scrollbar


- `assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js` (0.17 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss` (1.75 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/pickr


- `assets/vendor/libs/pickr/_pickr-classic.scss` (0.3 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/pickr/_pickr-monolith.scss` (0.35 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/pickr/_pickr-nano.scss` (0.13 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/pickr/pickr.js` (0.12 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/pickr/pickr-themes.scss` (0.86 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/plyr


- `assets/vendor/libs/plyr/plyr.js` (0.08 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/plyr/plyr.scss` (3.93 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/popper


- `assets/vendor/libs/popper/popper.js` (0.25 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/quill


- `assets/vendor/libs/quill/_mixins.scss` (1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/quill/editor.scss` (13.44 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/quill/katex.js` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/quill/katex.scss` (0.03 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/quill/quill.js` (0.1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/quill/typography.scss` (1.38 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/raty-js


- `assets/vendor/libs/raty-js/raty-js.js` (0.09 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/raty-js/raty-js.scss` (0.22 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/select2


- `assets/vendor/libs/select2/select2.js` (0.13 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/select2/select2.scss` (11.14 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/shepherd


- `assets/vendor/libs/shepherd/shepherd.js` (0.11 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/shepherd/shepherd.scss` (3 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/sortablejs


- `assets/vendor/libs/sortablejs/sortable.js` (0.12 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/spinkit


- `assets/vendor/libs/spinkit/spinkit.scss` (0.2 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/sweetalert2


- `assets/vendor/libs/sweetalert2/sweetalert2.js` (0.31 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/sweetalert2/sweetalert2.scss` (4.42 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/swiper


- `assets/vendor/libs/swiper/swiper.js` (0.1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/swiper/swiper.scss` (1.28 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/tagify


- `assets/vendor/libs/tagify/_tagify-email-list.scss` (1.26 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/tagify/_tagify-inline-suggestion.scss` (0.71 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/tagify/_tagify-users-list.scss` (1.23 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/tagify/tagify.js` (0.1 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/tagify/tagify.scss` (5.21 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/libs/typeahead-js


- `assets/vendor/libs/typeahead-js/typeahead.js` (0.04 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.
- `assets/vendor/libs/typeahead-js/typeahead.scss` (1.4 KB): Quando precisar identificar dependencias third-party usadas pelo template e seu ponto de integracao.


## Categoria: assets/vendor/scss


- `assets/vendor/scss/_bootstrap.scss` (1.22 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended.scss` (1.21 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_accordion.scss` (8.36 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_alert.scss` (3.27 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_badge.scss` (2.38 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_breadcrumb.scss` (1.03 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_button-group.scss` (4.46 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_buttons.scss` (14.31 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_card.scss` (7.2 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_carousel.scss` (0.75 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_dropdown.scss` (2.03 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_forms.scss` (0.33 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_functions.scss` (1.36 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_helpers.scss` (0.03 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_include.scss` (0.79 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_include-dark.scss` (0.72 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_list-group.scss` (5.79 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_mixins.scss` (0.13 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_modal.scss` (7.71 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_nav.scss` (10.48 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_navbar.scss` (3.45 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_offcanvas.scss` (1.02 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_pagination.scss` (7.55 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_popover.scss` (3.38 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_progress.scss` (1.01 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_reboot.scss` (1.32 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_root.scss` (4.11 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_skin.scss` (5.34 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_spinners.scss` (0.72 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_tables.scss` (3.58 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_toasts.scss` (1.38 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_tooltip.scss` (2.15 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_type.scss` (0.31 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_utilities.scss` (26.3 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_variables.scss` (43.77 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/_variables-dark.scss` (10.92 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_floating-labels.scss` (0.83 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_form-check.scss` (3.07 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_form-control.scss` (2.8 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_form-range.scss` (1.31 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_form-select.scss` (2.79 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_form-text.scss` (0.09 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_input-group.scss` (10.72 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_labels.scss` (0.37 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/forms/_validation.scss` (6.88 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/helpers/_color-bg.scss` (1.22 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/mixins/_border-radius.scss` (1.03 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/mixins/_caret.scss` (1.4 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_bootstrap-extended/mixins/_misc.scss` (0.37 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_colors.scss` (1.56 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components.scss` (0.45 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_app-brand.scss` (1.43 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_avatar.scss` (3.07 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_base.scss` (1.49 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_common.scss` (0.92 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_custom-options.scss` (3.39 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_footer.scss` (3.74 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_include.scss` (0.5 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_layout.scss` (28.35 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_menu.scss` (17.62 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_mixins.scss` (0.02 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_root.scss` (2.43 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_search.scss` (5.53 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_switch.scss` (7.98 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_text-divider.scss` (3.36 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_timeline.scss` (5.81 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_variables.scss` (9.44 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/_variables-dark.scss` (0.48 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_components/mixins/_switch.scss` (1.77 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_custom-styles.scss` (0.1 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_custom-variables/_bootstrap-extended.scss` (0.51 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_custom-variables/_bootstrap-extended-dark.scss` (0.49 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_custom-variables/_components.scss` (0.42 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/_custom-variables/_components-dark.scss` (0.46 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/core.scss` (0.11 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-academy.scss` (0.69 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-academy-details.scss` (0.22 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-calendar.scss` (2.26 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-chat.scss` (10.16 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-ecommerce.scss` (0.68 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-email.scss` (10.35 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-invoice.scss` (0.78 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-invoice-print.scss` (0.46 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-kanban.scss` (5.12 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-logistics-dashboard.scss` (1.2 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/app-logistics-fleet.scss` (2.74 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/cards-advance.scss` (1.74 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front/_common.scss` (0.84 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front/_footer.scss` (1.65 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front/_navbar.scss` (3.94 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front/_variables.scss` (0.05 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front-page.scss` (0.33 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front-page-help-center.scss` (0.45 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front-page-landing.scss` (5.61 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front-page-payment.scss` (0.28 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/front-page-pricing.scss` (0.77 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-auth.scss` (4.23 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-faq.scss` (0.72 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-icons.scss` (0.82 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-misc.scss` (0.83 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-pricing.scss` (0.21 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-profile.scss` (0.86 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/page-user-view.scss` (0.15 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/ui-carousel.scss` (1.08 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.
- `assets/vendor/scss/pages/wizard-ex-checkout.scss` (0.44 KB): Quando precisar ajustar tema/design tokens/estilo base sem quebrar o padrao Vuexy.


## Categoria: css


- `css/app.css` (0 KB): Quando precisar de estilos globais demonstrativos do template.


## Categoria: js


- `js/app.js` (0.15 KB): Quando precisar de entrypoints JS principais do setup Laravel/Vite de referencia.
- `js/bootstrap.js` (0.12 KB): Quando precisar de entrypoints JS principais do setup Laravel/Vite de referencia.
- `js/laravel-user-management.js` (27.96 KB): Quando precisar de entrypoints JS principais do setup Laravel/Vite de referencia.


## Categoria: menu


- `menu/horizontalMenu.json` (32.63 KB): Quando precisar definir estrutura de navegacao (menus vertical e horizontal em JSON).
- `menu/verticalMenu.json` (28.68 KB): Quando precisar definir estrutura de navegacao (menus vertical e horizontal em JSON).


## Categoria: views/_partials


- `views/_partials/_modals/modal-add-new-address.blade.php` (8.78 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-add-new-cc.blade.php` (2.77 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-add-permission.blade.php` (1.57 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-add-role.blade.php` (12.44 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-create-app.blade.php` (17.79 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-edit-cc.blade.php` (2.81 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-edit-permission.blade.php` (1.78 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-edit-user.blade.php` (5.94 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-enable-otp.blade.php` (1.43 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-pricing.blade.php` (6.94 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-refer-earn.blade.php` (12.4 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-select-payment-methods.blade.php` (3.25 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-select-payment-providers.blade.php` (10.45 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-share-project.blade.php` (12.16 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-two-factor-auth.blade.php` (5.5 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_modals/modal-upgrade-plan.blade.php` (1.97 KB): Quando precisar de modal padrao para reaproveitar em telas reais do projeto.
- `views/_partials/_offcanvas/offcanvas-add-payment.blade.php` (2.11 KB): Quando precisar de offcanvas lateral padrao para fluxos de formulario ou listagem.
- `views/_partials/_offcanvas/offcanvas-send-invoice.blade.php` (2.08 KB): Quando precisar de offcanvas lateral padrao para fluxos de formulario ou listagem.
- `views/_partials/macros.blade.php` (0.99 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.
- `views/_partials/wizard-ex-checkout.blade.php` (30.23 KB): Quando precisar consultar referencia tecnica ou visual desse arquivo especifico no Vuexy.


## Categoria: views/content


- `views/content/apps/app-academy-course.blade.php` (20.47 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-academy-course-details.blade.php` (13.81 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-academy-dashboard.blade.php` (26.94 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-access-permission.blade.php` (1.47 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-access-roles.blade.php` (11.5 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-calendar.blade.php` (8.42 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-chat.blade.php` (31.19 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-category-list.blade.php` (5.77 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-customer-all.blade.php` (7.22 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-customer-details-billing.blade.php` (27 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-customer-details-notifications.blade.php` (10.72 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-customer-details-overview.blade.php` (10.27 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-customer-details-security.blade.php` (11.8 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-dashboard.blade.php` (34.03 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-manage-reviews.blade.php` (4.98 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-order-details.blade.php` (8.74 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-order-list.blade.php` (3.45 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-product-add.blade.php` (21.67 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-product-list.blade.php` (4.23 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-referrals.blade.php` (15.14 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-checkout.blade.php` (7.65 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-details.blade.php` (13.96 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-locations.blade.php` (7.65 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-notifications.blade.php` (12 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-payments.blade.php` (6.47 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-ecommerce-settings-shipping.blade.php` (12.71 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-email.blade.php` (54.36 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-invoice-add.blade.php` (13.07 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-invoice-edit.blade.php` (13.52 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-invoice-list.blade.php` (3.55 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-invoice-preview.blade.php` (7.49 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-invoice-print.blade.php` (4.43 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-kanban.blade.php` (11.26 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-logistics-dashboard.blade.php` (30 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-logistics-fleet.blade.php` (17.52 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-list.blade.php` (8.98 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-view-account.blade.php` (12.62 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-view-billing.blade.php` (15.72 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-view-connections.blade.php` (14.68 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-view-notifications.blade.php` (11.24 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/apps/app-user-view-security.blade.php` (12.38 KB): Quando precisar de referencia visual para paginas de aplicacao (CRUDs, listas, detalhes e dashboards).
- `views/content/authentications/auth-forgot-password-basic.blade.php` (2.29 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-forgot-password-cover.blade.php` (3.08 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-login-basic.blade.php` (4.31 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-login-cover.blade.php` (4.89 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-register-basic.blade.php` (4.38 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-register-cover.blade.php` (5.03 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-register-multisteps.blade.php` (20.97 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-reset-password-basic.blade.php` (3.26 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-reset-password-cover.blade.php` (4.03 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-two-steps-basic.blade.php` (3.26 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-two-steps-cover.blade.php` (4.1 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-verify-email-basic.blade.php` (1.36 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/authentications/auth-verify-email-cover.blade.php` (2.23 KB): Quando precisar de referencia para autenticacao (login, cadastro, reset e 2FA).
- `views/content/cards/cards-actions.blade.php` (15.78 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/cards/cards-advance.blade.php` (97.02 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/cards/cards-analytics.blade.php` (30.06 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/cards/cards-basic.blade.php` (29.22 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/cards/cards-statistics.blade.php` (20.47 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/charts/charts-apex.blade.php` (13.44 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/charts/charts-chartjs.blade.php` (11.08 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/dashboard/dashboards-analytics.blade.php` (34.11 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/dashboard/dashboards-crm.blade.php` (33.61 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-avatar.blade.php` (10.77 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-blockui.blade.php` (7.37 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-drag-and-drop.blade.php` (18.47 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-media-player.blade.php` (1.57 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-misc.blade.php` (6.37 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-perfect-scrollbar.blade.php` (6.01 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-star-ratings.blade.php` (5.65 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-sweetalert2.blade.php` (5.29 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-text-divider.blade.php` (6.9 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-timeline-basic.blade.php` (9.02 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-timeline-fullscreen.blade.php` (20.65 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-tour.blade.php` (0.74 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/extended-ui/extended-ui-treeview.blade.php` (3.65 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-basic-inputs.blade.php` (25.79 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-custom-options.blade.php` (33.61 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-editors.blade.php` (4.37 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-extras.blade.php` (7.97 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-file-upload.blade.php` (1.86 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-input-groups.blade.php` (19.92 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-pickers.blade.php` (8.05 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-selects.blade.php` (24.29 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-sliders.blade.php` (7.5 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-elements/forms-switches.blade.php` (15.38 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-layout/form-layouts-horizontal.blade.php` (50.2 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-layout/form-layouts-sticky.blade.php` (16.37 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-layout/form-layouts-vertical.blade.php` (37.92 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-validation/form-validation.blade.php` (21.13 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-wizard/form-wizard-icons.blade.php` (47.16 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/form-wizard/form-wizard-numbered.blade.php` (47.02 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/front-pages/checkout-page.blade.php` (1.46 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/front-pages/help-center-article.blade.php` (3.9 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/front-pages/help-center-landing.blade.php` (65.03 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/front-pages/landing-page.blade.php` (67.95 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/front-pages/payment-page.blade.php` (8.33 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/front-pages/pricing-page.blade.php` (18.83 KB): Quando precisar de landing/front pages fora do painel interno.
- `views/content/icons/icons-font-awesome.blade.php` (6.66 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/icons/icons-tabler.blade.php` (6.9 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/laravel-example/user-management.blade.php` (9.07 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-blank.blade.php` (0.14 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-collapsed-menu.blade.php` (0.87 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-container.blade.php` (1.11 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-content-navbar.blade.php` (0.69 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-content-navbar-with-sidebar.blade.php` (1.08 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-fluid.blade.php` (1.07 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-without-menu.blade.php` (1.13 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/layouts-example/layouts-without-navbar.blade.php` (0.74 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/maps/maps-leaflet.blade.php` (2.46 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/modal/modal-examples.blade.php` (7.48 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-account-settings-account.blade.php` (10.8 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-account-settings-billing.blade.php` (14.35 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-account-settings-connections.blade.php` (9.45 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-account-settings-notifications.blade.php` (6.42 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-account-settings-security.blade.php` (12.52 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-faq.blade.php` (19.29 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-misc-comingsoon.blade.php` (1.38 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-misc-error.blade.php` (1.16 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-misc-not-authorized.blade.php` (1.22 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-misc-under-maintenance.blade.php` (1.21 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-pricing.blade.php` (6.56 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-profile-connections.blade.php` (16.07 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-profile-projects.blade.php` (29.17 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-profile-teams.blade.php` (33.06 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/pages/pages-profile-user.blade.php` (22.3 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/tables/tables-basic.blade.php` (133.82 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/tables/tables-datatables-advanced.blade.php` (6.35 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/tables/tables-datatables-basic.blade.php` (6.92 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/tables/tables-datatables-extensions.blade.php` (3.34 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-accordion.blade.php` (16.39 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-alerts.blade.php` (11.07 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-badges.blade.php` (24.41 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-buttons.blade.php` (27.43 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-carousel.blade.php` (16.95 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-collapse.blade.php` (5.94 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-dropdowns.blade.php` (42.91 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-footer.blade.php` (14.79 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-list-groups.blade.php` (31.5 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-modals.blade.php` (71.09 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-navbar.blade.php` (30.83 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-offcanvas.blade.php` (15.42 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-pagination-breadcrumbs.blade.php` (40.91 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-progress.blade.php` (10.23 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-spinners.blade.php` (10.74 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-tabs-pills.blade.php` (22.14 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-toasts.blade.php` (22.37 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-tooltips-popovers.blade.php` (9.29 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/user-interface/ui-typography.blade.php` (13.8 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/wizard-example/wizard-ex-checkout.blade.php` (1.27 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/wizard-example/wizard-ex-create-deal.blade.php` (28.21 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.
- `views/content/wizard-example/wizard-ex-property-listing.blade.php` (37.92 KB): Quando precisar de exemplo visual de componente ou pagina para replicar no projeto real.


## Categoria: views/layouts


- `views/layouts/blankLayout.blade.php` (0.33 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/commonMaster.blade.php` (4.63 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/contentNavbarLayout.blade.php` (2.63 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/horizontalLayout.blade.php` (2.52 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/layoutFront.blade.php` (0.31 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/layoutMaster.blade.php` (0.49 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/footer/footer.blade.php` (1.57 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/footer/footer-front.blade.php` (10.83 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/menu/horizontalMenu.blade.php` (2.49 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/menu/submenu.blade.php` (1.76 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/menu/verticalMenu.blade.php` (2.65 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/navbar/navbar.blade.php` (0.65 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/navbar/navbar-front.blade.php` (14.94 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/navbar/navbar-partial.blade.php` (26.02 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/scripts.blade.php` (0.89 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/scriptsFront.blade.php` (0.65 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/scriptsIncludes.blade.php` (2.94 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/scriptsIncludesFront.blade.php` (2 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/styles.blade.php` (1.09 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).
- `views/layouts/sections/stylesFront.blade.php` (0.81 KB): Quando precisar criar ou ajustar estrutura base de layout Blade (master, navbar, menu, secoes de script/style).

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- eslint (ESLINT) - v8
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `livewire-development` — Develops reactive Livewire 3 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `yarn run build`, `yarn run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `yarn run build` or ask the user to run `yarn run dev` or `composer run dev`.

=== laravel/v11 rules ===

# Laravel 11

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Laravel 11 brought a new streamlined file structure which this project now uses.

## Laravel 11 Structure

- In Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- No app\Console\Kernel.php - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Commands auto-register - files in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

## New Artisan Commands

- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
