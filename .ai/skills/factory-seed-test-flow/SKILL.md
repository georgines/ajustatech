---
name: factory-seed-test-flow
description: "Use quando criar ou evoluir recursos com factories, seeders e testes de feature, incluindo dados relacionais e povoamento de modulo."
---

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
6. garantir preenchimento correto dos relacionamentos e dados relacionais.
7. cobrir funcionalidades do modulo/submodulo (todas as features relevantes).

## Fluxo recomendado
1. Criar factory em `Database/Factories`.
2. Atualizar seeder do modulo para usar factory.
3. Garantir relacionamentos corretos no seed/factory (FKs e coerencia de dominio).
4. Criar/ajustar testes:
   - Banco
   - Services
   - Livewire
5. Garantir cobertura das funcionalidades do modulo/submodulo.
6. Rodar:
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
   - `php artisan test --testsuite=Feature`

## Checklist rapido
- [ ] Factory valida para o recurso.
- [ ] Seed usando factory.
- [ ] Relacionamentos preenchidos corretamente nos dados seedados.
- [ ] Teste de banco cobrindo persistencia e filtros.
- [ ] Teste de service cobrindo regras de negocio.
- [ ] Teste de Livewire cobrindo fluxo da tela.
- [ ] Funcionalidades do modulo/submodulo cobertas por testes.
- [ ] Povoamento global executado.

## Nao fazer
- Nao implementar recurso sem factory.
- Nao seedar dados manualmente quando houver factory.
- Nao ignorar relacionamento entre entidades ao seedar/fabricar dados.
- Nao fechar feature sem testes de Feature relevantes.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

