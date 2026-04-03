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
