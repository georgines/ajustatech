# Servicos e Funcionalidades Modulares

## Objetivo
Padronizar a organizacao interna dos modulos quando houver mais de uma funcionalidade de negocio, facilitando manutencao, escalabilidade e testes.

## Modelo de composicao (Modulo -> Funcionalidades)
Padrao recomendado: manter um modulo pai e criar funcionalidades (submodulos de recurso) dentro dele.

Exemplo real de referencia:
- Modulo pai: `ServiceOrder`
- Funcionalidade/recurso: `Procedure`

Fluxo de registro:
1. O provider do modulo pai registra o provider da funcionalidade no `register()`.
2. O provider da funcionalidade carrega rotas, migrations, bindings, comandos e componentes da propria funcionalidade.
3. O provider do modulo pai continua responsavel pelos itens centrais do modulo (menus, rotas base, views base, comandos de modulo).

## Regra 1: Separacao por funcionalidade
Quando um modulo tiver duas ou mais funcionalidades, separar por pasta de funcionalidade dentro das pastas padrao do modulo.

Exemplos esperados:
- `Livewire/<Funcionalidade>/...`
- `Views/livewire/<funcionalidade-kebab-case>/...`
- `Tests/Feature/Livewire/<Funcionalidade>/...`
- `Tests/Feature/Database/<Funcionalidade>/...` (quando aplicavel)
- `Database/Migrations/<Funcionalidade>/...` (quando aplicavel)
- `Database/Seeders/<Funcionalidade>/...` (quando aplicavel)
- `Database/Factories/<Funcionalidade>/...` (quando aplicavel)
- `Providers/<Funcionalidade>/<Funcionalidade>ServiceProvider.php` (quando aplicavel)
- `Commands/<Funcionalidade>/...` (quando aplicavel)
- `storage/<disco>/<modulo>/<funcionalidade>/...` para arquivos de upload (quando aplicavel), incluindo disco local e remoto.

Regra de armazenamento:
- Cada modulo/funcionalidade com upload deve ter pasta propria de armazenamento no disco local e/ou remoto utilizado.
- Nao usar pasta raiz compartilhada entre funcionalidades diferentes.

## Regra 1.1: Tudo da funcionalidade dentro da pasta da funcionalidade
Para recurso novo, concentrar artefatos da feature na propria pasta de funcionalidade dentro do modulo:
- migration da feature em `Database/Migrations/<Funcionalidade>/`
- factory da feature em `Database/Factories/<Funcionalidade>/`
- seeder da feature em `Database/Seeders/<Funcionalidade>/`
- comandos da feature em `Commands/<Funcionalidade>/`
- provider da feature em `Providers/<Funcionalidade>/`

Depois, registrar no provider do modulo pai.

## Regra 2: Pasta Services obrigatoria
Todo modulo deve possuir a pasta:
- `modules/Ajustatech/<Modulo>/src/Services`

## Regra 3: Todo service deve ter interface
Cada service concreto deve possuir sua interface correspondente.

Padrao recomendado:
- Interface em `Services/<Funcionalidade>/Contracts/<NomeService>Interface.php`
- Implementacao em `Services/<Funcionalidade>/<NomeService>.php`
- Regra obrigatoria para funcionalidades novas: contrato deve ficar dentro da pasta da funcionalidade e nao em `Services/Contracts` global.

Alternativa aceita em modulo legado:
- Interface e implementacao na mesma pasta de `Services`, mantendo nomenclatura clara.

## Regra 3.1: Consultas no Model, nao no Service
- Toda consulta de banco (listagem, busca por ID, filtros e leituras relacionadas) deve ser encapsulada em Model (`Database/Models`), via scopes ou metodos de dominio.
- Services devem atuar como orquestradores de caso de uso, sem concentrar query SQL/Eloquent de leitura.
- Nao duplicar regra de consulta em Service, Livewire ou Controller quando ela pertencer ao dominio do modelo.

## Regra 4: Resolucao via container (bind)
Services devem ser consumidos por interface e resolvidos via container do Laravel.
Nao injetar implementacao concreta diretamente em controllers, Livewire ou jobs.

Registrar bindings no provider do modulo:
- `modules/Ajustatech/<Modulo>/src/Providers/<Modulo>ServiceProvider.php`
- Metodo `register()`

Exemplo:
```php
public function register(): void
{
    $this->app->bind(
        OrderWorkflowServiceInterface::class,
        OrderWorkflowService::class
    );
}
```

## Regra 4.1: Providers de funcionalidade
Provider da funcionalidade deve:
1. Fazer binds da funcionalidade no `register()`.
2. Registrar configuracoes de diretorios da funcionalidade (ex.: midia) no `register()`.
3. Carregar `Routes/<funcionalidade>.php` e `Database/Migrations/<Funcionalidade>` no `boot()`.
4. Registrar comandos da funcionalidade no `boot()`.
5. Registrar componentes Livewire da funcionalidade no `boot()`.

## Regra 4.2: Comandos em cadeia (manual)
Em comandos de modulo que orquestram features:
- Chamar comandos da feature explicitamente com `$this->call('feature:...')`.
- Evitar auto-descoberta dinamica de comandos via loop em `Artisan::all()` para fluxo principal.
- Em seeds de modulo, chamar explicitamente o seeder do modulo e depois os comandos de seed das features.

## Regra 5: Convencao de nomes
- Interface: sufixo `Interface`
- Implementacao: sufixo `Service`
- Nome por contexto de negocio, evitando nomes genericos como `MainService`.

## Regra 6: Otimizacao obrigatoria de consultas e limpeza de temporarios
- Em exibicao, carregamento, edicao e exclusao, reduzir ao maximo a quantidade de queries.
- Evitar N+1 com carregamento apropriado de relacoes e consultas em lote.
- Nao executar query dentro de loop.
- Em fluxo com upload, ao salvar definitivamente o arquivo, limpar/remover temporarios do processo para evitar acumulo de lixo em storage temporario.
- Cobrir os fluxos com testes de requisicoes/performance para validar que as otimizacoes foram aplicadas.

## Validacao final antes de concluir task
1. Existe separacao por funcionalidade nas pastas padrao?
2. O modulo possui `Services/`?
3. Todo service novo tem interface?
4. Binding interface -> implementacao foi registrado no provider?
5. Consumo em codigo esta por interface (DI), nao por classe concreta?
6. Provider do modulo pai registra providers das funcionalidades?
7. Cada funcionalidade registra seus proprios comandos/migrations/rotas no provider proprio?
