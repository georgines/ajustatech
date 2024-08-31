<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Str;

class MakeLivewireComponentCommand extends Command
{
    protected $signature = 'make:livewire-component {name} {path}';
    protected $description = 'Create a new Livewire 3 component in a specified path';
    protected $helper;

    protected $name;
    protected $path;
    protected $basePath;
    protected $namespace;
    protected $componentName;
    protected $kebabComponentName;
    protected $lowComponentName;
    protected $showComponentName;
    protected $showKebabComponentName;
    protected $managementComponentName;
    protected $managementKebabComponentName;
    protected $timestamp;


    public function __construct(CommandHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->helper->setBasePath(base_path());
        $this->helper->loadConfig();
    }

    public function handle()
    {
        $this->initializeProperties();

        $this->generateUseNamespaces();
        $this->generateComponentRegistration();
        $this->createProviderStub();
        $this->createMenuStubs();
        $this->generateRouteNamespaces();
        $this->generateRoutes();
        $this->createCommandAndDatabaseStubs();
        $this->createTestAndLivewireStubs();

        $this->info("Livewire component {$this->componentName} created successfully at {$this->basePath}.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $this->basePath = $this->helper->getBasePath();
        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->componentName = $this->helper->getClassName($this->name);
        $this->kebabComponentName = $this->helper->getKebabCaseName($this->name);
        $this->lowComponentName = Str::lower($this->componentName);
        $this->showComponentName = "Show" . $this->componentName;
        $this->showKebabComponentName = Str::kebab($this->showComponentName);
        $this->managementComponentName = $this->componentName . "Management";
        $this->managementKebabComponentName = Str::kebab($this->managementComponentName);
        $this->timestamp = $this->helper->generateMigrationTimestamp();
    }

    protected function generateUseNamespaces()
    {
        $namespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"],
            ['name' => "{$this->namespace}\Commands\Seed{$this->componentName}Command"],
        ];

        $useTemplate = "use {name};";
        $useNamespace = $this->helper->generateDynamicString($namespaceList, $useTemplate);

        $this->helper->addContents(['NAMESPACE_IMPORT' => $useNamespace]);
    }

    protected function generateComponentRegistration()
    {
        $components = [
            [
                'name' => $this->showKebabComponentName,
                'class' => "{$this->showComponentName}::class"
            ],
            [
                'name' => $this->managementKebabComponentName,
                'class' => "{$this->managementComponentName}::class"
            ]
        ];

        $template = "\t\tLivewire::component('{name}', {class});";
        $registredComponents = $this->helper->generateDynamicString($components, $template);

        $this->helper->addContents(['REGISTRED_COMPONENTS' => $registredComponents]);
    }

    protected function createProviderStub()
    {
        $this->helper->createStubFiles($this->basePath, $this->componentName, [
            ['module-provider.stub' => "{$this->namespace}/Providers/{$this->componentName}ServiceProvider.php"],
        ]);
    }

    protected function createMenuStubs()
    {
        $this->helper->addContents(["LOW_CLASS_NAME" => $this->lowComponentName]);

        $this->helper->createStubFiles($this->basePath, $this->componentName, [
            ['module-menu.stub' => "{$this->path}/Menu/horizontalMenu.json"],
            ['module-menu.stub' => "{$this->path}/Menu/verticalMenu.json"],
        ]);
    }

    protected function generateRouteNamespaces()
    {
        $routeNamespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"]
        ];

        $routeTemplate = "use {name};";
        $componentRoute = $this->helper->generateDynamicString($routeNamespaceList, $routeTemplate);

        $this->helper->addContents([
            "COMPONENT_ROUTE" => $componentRoute
        ]);
    }

    protected function generateRoutes()
    {
        $routes = [
            [
                'uri' => "/{$this->kebabComponentName}",
                'action' => "{$this->showComponentName}::class",
                'name' => "{$this->kebabComponentName}-show"
            ],
            [
                'uri' => "/{$this->kebabComponentName}/cadastro",
                'action' => "{$this->managementComponentName}::class",
                'name' => "{$this->kebabComponentName}-create"
            ],
            [
                'uri' => "/{$this->kebabComponentName}/{{$this->kebabComponentName}}/editar",
                'action' => "{$this->managementComponentName}::class",
                'name' => "{$this->kebabComponentName}-edit"
            ]
        ];

        $template = "Route::get('{uri}', {action})->name('{name}');";
        $registeredRoutes = $this->helper->generateDynamicString($routes, $template);

        $this->helper->addContents([
            "ROUTE_COMPONENTS" => $registeredRoutes
        ]);
    }

    protected function createCommandAndDatabaseStubs()
    {
        $this->helper->addContents([
            "LOW_CLASS_NAME" => $this->lowComponentName,
            "CLASS_NAME" => $this->componentName
        ]);

        $this->helper->createStubFiles($this->basePath, $this->componentName, [
            ['module-command.stub' => "{$this->namespace}/Commands/Seed{$this->componentName}Command.php"],
            ['module-factory.stub' => "{$this->namespace}/Database/Factories/{$this->componentName}Factory.php"],
            ['module-migration.stub' => "{$this->namespace}/Database/Migrations/{$this->timestamp}_create_{$this->lowComponentName}_table.php"],
            ['module-model.stub' => "{$this->namespace}/Database/Models/{$this->componentName}.php"],
            ['module-seeder.stub' => "{$this->namespace}/Database/Seeders/{$this->componentName}Seeder.php"],
            ['module-routes.stub' => "{$this->namespace}/Routes/web.php"],
            ['module-test-model.stub' => "{$this->namespace}/Tests/Feature/{$this->componentName}Test.php"]
        ]);
    }

    protected function createTestAndLivewireStubs()
    {
        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->showKebabComponentName,
            "COMPONENT_NAME" => $this->showComponentName
        ]);

        $this->helper->createStubFiles($this->basePath, $this->componentName, [
            ['module-test-component.stub' => "{$this->namespace}/Tests/Feature/{$this->componentName}/{$this->showComponentName}Test.php"],
            ['module-livewire-component.stub' => "{$this->namespace}/Livewire/{$this->showComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->namespace}/Views/livewire/{$this->showKebabComponentName}.blade.php"]
        ]);

        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->managementKebabComponentName,
            "COMPONENT_NAME" => $this->managementComponentName
        ]);

        $this->helper->createStubFiles($this->basePath, $this->componentName, [
            ['module-test-component.stub' => "{$this->namespace}/Tests/Feature/{$this->componentName}/{$this->managementComponentName}Test.php"],
            ['module-livewire-component' => "{$this->namespace}/Livewire/{$this->managementComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->namespace}/Views/livewire/{$this->managementKebabComponentName}.blade.php"],
        ]);
    }
}
