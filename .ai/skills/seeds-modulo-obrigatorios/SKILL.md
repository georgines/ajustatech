# Skill: Seeds Modulo Obrigatorios

## Objetivo
Garantir que todo recurso/modulo novo venha com seeds completos e que o povoamento global dos modulos seja executado.

## Regra obrigatoria
Sempre criar seeds de todas as possibilidades relevantes do recurso.

## Fluxo
1. Criar/atualizar seeders do modulo em `Database/Seeders`.
2. Garantir comando `module:seed-*` do recurso/modulo.
3. Executar povoamento completo:
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
4. Confirmar dados no banco para cenarios principais e de borda.

## Comandos chave
- `php artisan module:seed`
- `php artisan dev:seed`
- `php artisan dev:migrate`

## Nao fazer
- Nao criar recurso sem seeds correspondentes.
- Nao depender de dados manuais para validar feature.
- Nao finalizar tarefa sem rodar o seed global dos modulos.

