# Skill: Qualidade Profissional

## Objetivo
Padronizar a implementacao com nivel profissional, priorizando simplicidade, clareza, manutenibilidade e performance.

## Regra obrigatoria
- Escrever codigo como programador experiente, com legibilidade, coesao e responsabilidade clara por classe/metodo.
- Aplicar principios de Clean Code e SOLID nas decisoes de design, sem overengineering.
- Trabalhar com mentalidade agil: ciclos curtos, validacao continua e melhoria incremental.
- Preferir solucao simples e objetiva que resolve bem o problema de negocio.
- Otimizar modelagem e consultas de banco para evitar N+1.
- Em listagens e relacoes, usar eager loading, scopes e filtros no banco; nunca filtrar colecao grande em memoria.
- Garantir indices adequados para colunas filtradas/relacionadas e revisar plano de consulta quando necessario.

## Check rapido antes de concluir
1. O codigo ficou simples de ler e manter?
2. Houve aplicacao pratica de Clean Code e SOLID (sem excesso)?
3. Fluxos principais estao sem N+1?
4. Consultas criticas estao otimizadas e com indices coerentes?

## Referencias internas
- `/.ai/guidelines/projeto/padroes.md`
- `/.ai/guidelines/projeto/otimizacao-solicitacoes.md`
- `/.ai/guidelines/projeto/filtros-tabelas-modulo.md`
- `/.ai/guidelines/projeto/migrations-laravel-profissional.md`
