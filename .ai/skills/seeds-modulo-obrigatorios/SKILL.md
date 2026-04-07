# Skill: Seeds Modulo Obrigatorios

## Objetivo
Garantir que todo recurso/modulo novo venha com seeds completos e que o povoamento global dos modulos seja executado.

## Regra obrigatoria
Sempre criar seeds de todas as possibilidades relevantes do recurso.
- Sempre respeitar relacionamentos do dominio ao seedar (dados relacionais corretos e consistentes).
- Sempre cobrir funcionalidades do modulo e submodulos (features da funcionalidade).

## Fluxo
1. Criar/atualizar seeders do modulo em `Database/Seeders`.
2. Garantir comando `module:seed-*` do recurso/modulo.
3. Garantir que os dados relacionais estao corretos (FKs, dependencias e cenarios integrados).
4. Executar povoamento completo:
   - `php artisan dev:migrate`
   - `php artisan dev:seed`
5. Confirmar dados no banco para cenarios principais, de borda e relacionais.

## Comandos chave
- `php artisan module:seed`
- `php artisan dev:seed`
- `php artisan dev:migrate`

## Nao fazer
- Nao criar recurso sem seeds correspondentes.
- Nao depender de dados manuais para validar feature.
- Nao finalizar tarefa sem rodar o seed global dos modulos.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

