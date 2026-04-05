# Skill: Modulo Servicos por Funcionalidade

## Objetivo
Aplicar padrao de separacao por funcionalidade dentro do modulo e enforce de Services com Interface + bind no provider.

## Quando usar
- Quando houver pedido para criar mais de uma funcionalidade no mesmo modulo.
- Quando criar ou refatorar services de um modulo.
- Quando ajustar arquitetura interna para melhorar manutencao.

## Fonte de verdade
- `/.ai/guidelines/projeto/servicos-e-funcionalidades-modulares.md`
- `/.ai/guidelines/projeto/padroes.md`

## Checklist Operacional
1. Identificar funcionalidades que devem virar pastas dedicadas.
2. Reorganizar pastas padrao por funcionalidade (Livewire, Views, Tests, Database quando aplicavel).
   - Inclui migrations em `Database/Migrations/<Funcionalidade>/...` para funcionalidades novas.
3. Garantir existencia de `Services/` no modulo.
4. Criar interface para cada service novo dentro da propria funcionalidade (`Services/<Funcionalidade>/Contracts`), sem usar `Services/Contracts` global para feature nova.
5. Garantir que consumidores usem a interface (DI).
6. Registrar binds no `register()` do provider do modulo.
7. Validar testes e imports apos reorganizacao.

## Nao fazer
- Nao misturar multiplas funcionalidades em uma unica pasta sem separacao.
- Nao injetar service concreto diretamente em componentes/controladores.
- Nao criar service sem interface correspondente.
- Nao criar contrato de feature nova fora da pasta da funcionalidade.
