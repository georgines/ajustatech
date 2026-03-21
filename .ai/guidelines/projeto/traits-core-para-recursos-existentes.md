# Traits no Core para Recursos Existentes

## Regra principal
Sempre que for necessario implementar um recurso em algo que ja existe, criar uma **Trait** e colocar em:

- `modules/Ajustatech/Core/src/Traits`

## Objetivo
Evitar duplicacao de logica em classes existentes e manter extensoes reutilizaveis entre modulos.

## Quando aplicar
- Quando uma classe existente precisar ganhar comportamento novo sem inflar a propria classe.
- Quando a mesma logica puder ser reutilizada em mais de um modulo/componente/model.
- Quando o recurso for transversal (cross-cutting) no projeto.

## Como aplicar
1. Criar a trait no Core (`Core/src/Traits`).
2. Dar nome claro orientado ao comportamento.
3. Manter metodos coesos e focados no recurso.
4. Importar a trait (`use`) na classe do modulo que precisa dela.
5. Cobrir com testes de Feature (e Unit quando fizer sentido).

## Exemplo real no projeto
- `SwitchAlertDispatch` em `modules/Ajustatech/Core/src/Traits`
- Consumida em componentes Livewire de modulos como `Customer`.

## Beneficios
- Reuso padronizado.
- Menos acoplamento e menos codigo repetido.
- Evolucao centralizada de comportamento compartilhado.

