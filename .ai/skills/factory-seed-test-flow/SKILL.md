# Skill: Factory Seed Test Flow

## Objetivo
Garantir que todo recurso novo tenha base de dados consistente via factory, seed e testes completos de Feature.

## Regra obrigatoria
Para cada recurso/modulo novo:
1. criar/atualizar factories;
2. usar factories nos seeds;
3. usar factories nos testes;
4. executar povoamento global;
5. validar suite de testes de Feature.

## Fluxo recomendado
1. Criar factory em `Database/Factories`.
2. Atualizar seeder do modulo para usar factory.
3. Criar/ajustar testes:
   - Banco
   - Services
   - Livewire
4. Rodar:
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
   - `php artisan test --testsuite=Feature`

## Checklist rapido
- [ ] Factory valida para o recurso.
- [ ] Seed usando factory.
- [ ] Teste de banco cobrindo persistencia e filtros.
- [ ] Teste de service cobrindo regras de negocio.
- [ ] Teste de Livewire cobrindo fluxo da tela.
- [ ] Povoamento global executado.

## Nao fazer
- Nao implementar recurso sem factory.
- Nao seedar dados manualmente quando houver factory.
- Nao fechar feature sem testes de Feature relevantes.

