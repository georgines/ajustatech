# Validacoes Reutilizaveis no Core

## Regra principal
Quando nao existir validacao para um campo, a validacao deve ser criada em:

- `modules/Ajustatech/Core/src/Rules`

## Objetivo
Centralizar regras de validacao reutilizaveis para evitar duplicacao entre modulos e manter consistencia de comportamento.

## Como aplicar
1. Verificar se a regra ja existe em `Core/src/Rules`.
2. Se nao existir, criar uma nova classe de regra em `Core/src/Rules`.
3. Implementar a regra seguindo o contrato de validacao do Laravel (`ValidationRule`).
4. No modulo que precisar da validacao, importar e usar a regra no `validate()`/Form Request.

## Uso no modulo
- A regra criada no Core deve ser chamada explicitamente no modulo consumidor.
- Exemplo de import em modulo:
  - `use Ajustatech\Core\Rules\NomeDaRegra;`
- Exemplo de uso:
  - `'campo' => ['required', new NomeDaRegra]`

## Beneficios
- Reuso entre modulos.
- Menos codigo duplicado.
- Padrao unico de validacao para campos equivalentes.
- Manutencao mais simples.

## Referencia pratica no projeto
Ja existem regras compartilhadas no Core, como:
- `CpfValidator`
- `CnpjValidation`

Essas regras sao consumidas por modulos como `Customer`.

