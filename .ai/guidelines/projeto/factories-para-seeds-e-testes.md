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
