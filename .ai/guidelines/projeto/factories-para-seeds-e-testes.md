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

## Regras para seeds
- Todo recurso novo deve ter dados seedados com variacoes de negocio.
- Sempre executar povoamento global apos implementar:
  - `php artisan dev:migrate`
  - `php artisan dev:seed`

## Regras para testes
- Cobertura esperada por recurso:
  - teste de banco (persistencia/relacoes/filtros);
  - teste de service (regras de negocio);
  - teste de Livewire (fluxo de interface/comportamento).
- Fluxo TDD:
  - primeiro teste falha;
  - depois implementa;
  - por fim teste passa.

## Criterio de pronto
Uma implementacao so e considerada pronta quando:
- factory do recurso existe e esta valida;
- seed usa factory e foi executado;
- testes de Feature (Livewire/banco/services) estao cobrindo o recurso;
- povoamento global dos modulos foi executado sem erro.

