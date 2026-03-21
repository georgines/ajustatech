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

