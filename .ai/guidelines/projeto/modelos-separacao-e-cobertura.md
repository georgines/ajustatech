# Modelos Separados e Cobertura Obrigatoria

## Objetivo
Garantir separacao de responsabilidades do dominio e padrao minimo de persistencia e testes para todo modelo de modulo.

## Regra 1: Modelo vive em Models
Modelos de dominio do modulo devem ficar exclusivamente em:
- `modules/Ajustatech/<Modulo>/src/Database/Models`

Nao colocar codigo de modelo em:
- `Livewire/`
- `Services/`
- `Controllers/`
- `Commands/`
- qualquer outro arquivo que nao seja responsavel por modelo.

## Regra 2: Sem logica de modelo fora do modelo
Responsabilidades de modelo (relacionamentos, casts, scopes, accessors/mutators e regras de persistencia do proprio modelo) devem permanecer no arquivo do modelo.

Services, componentes e controllers podem orquestrar fluxo, mas nao devem replicar comportamento interno do modelo.

Inclui obrigatoriamente:
- consultas de leitura/listagem;
- consultas para carregamento de dados de edicao;
- consultas para fluxo de exclusao;
- consultas para carregamento de relacoes.

Services nao devem concentrar consultas de dados; devem chamar metodos/scopes do Model.

## Regra 3: Todo modelo deve ser completo
Para cada modelo novo ou relevante do modulo, garantir:
1. Migration correspondente.
2. Factory correspondente.
3. Seeder correspondente.

Estrutura esperada:
- `Database/Migrations/*create_<tabela>_table.php`
- `Database/Factories/<Model>Factory.php`
- `Database/Seeders/<Model>Seeder.php` (ou seeder equivalente por funcionalidade)

## Regra 4: Testes e seeds seguem fontes oficiais
Para evitar duplicidade de regra, usar como fonte oficial:
- `/.ai/guidelines/projeto/testes-feature-tdd.md` (fluxo RED -> GREEN -> REFACTOR).
- `/.ai/guidelines/projeto/factories-para-seeds-e-testes.md` (factory + seeds + cobertura).

Este guideline define especificamente a separacao de responsabilidades do modelo e a obrigatoriedade de migration/factory/seeder por modelo.

## Criterio de conclusao
Nao considerar task concluida sem:
1. Modelo no local correto.
2. Migration + Factory + Seeder presentes.
3. Regras de testes aplicadas conforme guidelines oficiais citadas acima.
