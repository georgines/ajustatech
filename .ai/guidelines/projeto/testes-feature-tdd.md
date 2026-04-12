# Testes de Feature (TDD)

## Fluxo obrigatorio
1. RED: criar teste e comprovar falha.
2. GREEN: implementar minimo para passar.
3. REFACTOR: limpar sem quebrar comportamento.

## Cobertura minima por mudanca
- Caso valido.
- Caso invalido.
- Persistencia/relacionamento quando houver banco.
- Seguranca e performance quando houver superficie sensivel.

## Recurso com persistencia
- Garantir migration, factory e seeder coerentes.
- Priorizar factories em testes, evitando dado manual repetido.

## Finalizacao
- Executar suite relevante.
- Rodar `php artisan dev:reinstall` ao final.
