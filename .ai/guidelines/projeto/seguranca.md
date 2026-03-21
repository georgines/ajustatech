# Seguranca e Integridade

## Escopo
Diretrizes de seguranca para Laravel + Livewire no contexto modular Ajustatech.

## Validacao
- Validar entradas no componente Livewire e/ou Form Request antes de persistir.
- Reutilizar regras do Core quando aplicavel (`CpfValidator`, `CnpjValidation`).
- Em edicao, usar `Rule::unique(...)->ignore($id)` para evitar falso positivo de unicidade.

## Mass Assignment
- Garantir `fillable` consistente em todos os Models modulares.
- Nunca usar `guarded = []` sem justificativa formal.

## Integridade transacional
- Operacoes financeiras devem usar transacao quando houver mais de uma escrita relacionada.
- Manter consistencia entre transacoes e saldos (ex.: cash + balances + transactions).

## Eventos de UI sensiveis
- Confirmacoes de acao devem passar por `SwitchAlertDispatch` quando houver impacto em dados.
- Evitar executar alteracoes destrutivas sem confirmacao.

## Rotas e autorizacao
- Cada rota de modulo deve ser nomeada e preparada para middleware/policies.
- Nao assumir que tela Livewire ja implica autorizacao.

## Seed e ambiente
- Seeds modulares (`module:seed-*`) devem ser idempotentes quando possivel.
- Evitar dados sensiveis hardcoded.

## Referencia Vuexy
`templete/Vuexy/resources` e apenas referencia de layout.
- Nao inserir regras de negocio ali.
- Nao usar pasta de referencia como origem de assets sensiveis de producao.

## Pontos de atencao atuais do projeto
- Validar encoding UTF-8 em textos PT-BR para evitar caracteres corrompidos.
- Revisar consistencia de nomes de pasta `menu` vs `Menu` entre modulos para compatibilidade cross-platform.
