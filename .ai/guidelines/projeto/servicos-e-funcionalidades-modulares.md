# Servicos e Funcionalidades Modulares

## Objetivo
Padronizar a organizacao interna dos modulos quando houver mais de uma funcionalidade de negocio, facilitando manutencao, escalabilidade e testes.

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

## Regra 5: Convencao de nomes
- Interface: sufixo `Interface`
- Implementacao: sufixo `Service`
- Nome por contexto de negocio, evitando nomes genericos como `MainService`.

## Validacao final antes de concluir task
1. Existe separacao por funcionalidade nas pastas padrao?
2. O modulo possui `Services/`?
3. Todo service novo tem interface?
4. Binding interface -> implementacao foi registrado no provider?
5. Consumo em codigo esta por interface (DI), nao por classe concreta?
