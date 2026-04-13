# Seguranca e Integridade

## Regras
- Proteger mass assignment (`fillable` coerente; evitar `guarded = []` sem justificativa).
- Validar e autorizar antes de persistir alteracoes sensiveis.
- Em operacoes com multiplas escritas relacionadas, usar transacao.
- Em Livewire, tratar propriedade publica como entrada nao confiavel.

## Acoes destrutivas
- Fluxos de delete/remove/destroy devem exigir confirmacao explicita antes da persistencia.

## Rotas
- Nomear rotas de modulo e preparar para middleware/policies.

## Seeds
- Evitar dados sensiveis hardcoded.
- Priorizar idempotencia quando fizer sentido.

## UI referencia
- `templete/Vuexy/resources` e referencia visual, nao fonte de negocio.
