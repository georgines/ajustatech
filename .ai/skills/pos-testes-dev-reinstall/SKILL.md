# Skill: Pos-Testes com `dev:reinstall`

## Objetivo
Padronizar o passo final de reconstruir e popular o banco apos rodar testes.

## Quando usar
- Sempre que testes forem executados durante uma task.
- Especialmente quando houver alteracao em migrations, models, factories ou seeders.

## Fonte de verdade
- `/.ai/guidelines/projeto/pos-testes-dev-reinstall.md`

## Procedimento obrigatorio
1. Rodar os testes necessarios da task.
2. Confirmar resultado dos testes.
3. Rodar:
   - `php artisan dev:reinstall`
4. Validar sucesso do comando sem erros.
5. Registrar no resumo final que o banco foi reconstruido e preenchido.

## Criterios de aceite
- Testes concluidos.
- `dev:reinstall` concluido com sucesso.
- Ambiente pronto para validacao manual com dados seedados.

## Nao fazer
- Nao encerrar tarefa com testes rodados sem executar `dev:reinstall`.
- Nao assumir banco valido sem confirmar sucesso do comando.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

