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
2. Reorganizar pastas padrao por funcionalidade (Livewire, Views, Tests, Database, Providers e Commands quando aplicavel).
   - Inclui migrations em `Database/Migrations/<Funcionalidade>/...` para funcionalidades novas.
   - Inclui comandos da feature em `Commands/<Funcionalidade>/...`.
   - Inclui provider da feature em `Providers/<Funcionalidade>/<Funcionalidade>ServiceProvider.php`.
3. Garantir existencia de `Services/` no modulo.
4. Criar interface para cada service novo dentro da propria funcionalidade (`Services/<Funcionalidade>/Contracts`), sem usar `Services/Contracts` global para feature nova.
5. Garantir que consumidores usem a interface (DI).
6. Registrar binds no `register()` do provider da funcionalidade.
7. Registrar provider da funcionalidade no provider do modulo pai.
8. Em comandos de modulo (seed/wipe), chamar comandos de feature manualmente com `$this->call(...)`, sem auto-descoberta dinamica.
9. Garantir que consultas de dados fiquem nos Models, nao no Service.
10. Otimizar queries de exibicao, carregamento, edicao e exclusao (sem query em loop).
11. Em fluxo de upload, garantir limpeza de arquivos temporarios apos salvar.
12. Validar testes, imports e testes de requisicoes/performance apos reorganizacao.

## Exemplo de composicao
- Modulo pai: `ServiceOrder`
- Funcionalidade: `Procedure`
- `ServiceOrderServiceProvider` registra `ProcedureServiceProvider`.
- `ProcedureServiceProvider` registra routes/migrations/livewire/commands/bindings da feature.
- Comando `module:*` do modulo pai chama `feature:*` da funcionalidade de forma explicita.

## Nao fazer
- Nao misturar multiplas funcionalidades em uma unica pasta sem separacao.
- Nao injetar service concreto diretamente em componentes/controladores.
- Nao criar service sem interface correspondente.
- Nao criar contrato de feature nova fora da pasta da funcionalidade.
- Nao depender de descoberta automatica para orquestrar comandos de feature no fluxo principal.
- Nao concentrar consulta de dados no Service quando ela pertencer ao Model.
## Regra obrigatoria de qualidade profissional
- Seguir `/.ai/skills/qualidade-profissional/SKILL.md` como fonte unica de qualidade de codigo e performance de banco (incluindo prevencao de N+1).

