<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Facades\Artisan;

class MakeLivewireComponentCommand extends Command
{
    protected $signature = 'make:livewire-component {name} {path}';
    protected $description = 'Create a new Livewire 3 component in a specified path';
    protected $helper;

    protected $name;
    protected $path;
    protected $basePath;
    protected $namespace;
    protected $className;
    protected $kebabClassName;
    protected $lowClassName;
    protected $showComponentName;
    protected $showKebabComponentName;
    protected $managementComponentName;
    protected $managementKebabComponentName;
    protected $timestamp;
    protected $routeNamespaceImport;
    protected $routeDefinition;
    protected $providerNamespaceImport;
    protected $componentRegister;

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
        $this->callMakeModuleProviderCommand();
        $this->callMakeModuleMenuCommand();
        $this->callMakeModuleRoutesCommand();
        $this->callMakeModuleModelCommand();
        $this->callMakeModuleLivewireComponentCommand();
        $this->callMakeModuleComposerCommand();

        $this->info("Livewire component {$this->className} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $this->path = $this->path . "/src";

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->showComponentName = "Show" . $this->className;
        $this->showKebabComponentName = $this->helper->getKebabCaseName($this->showComponentName);
        $this->managementComponentName = $this->className . "Management";
        $this->managementKebabComponentName = $this->helper->getKebabCaseName($this->managementComponentName);
        $this->timestamp = $this->helper->generateMigrationTimestamp();
    }

    protected function generateUseNamespaces()
    {
        $namespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"],
            ['name' => "{$this->namespace}\Commands\Seed{$this->className}Command"],
        ];

        $useTemplate = "use {name};";
        $this->providerNamespaceImport = $this->helper->generateDynamicString($namespaceList, $useTemplate);
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

        $this->componentRegister = $this->helper->addContents(['REGISTRED_COMPONENTS' => $registredComponents]);
    }

    protected function callMakeModuleProviderCommand()
    {
        $this->generateUseNamespaces();
        $this->generateComponentRegistration();

        Artisan::call('make:module-provider', [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $this->providerNamespaceImport,
            'component-register' => $this->componentRegister
        ]);
    }

    protected function callMakeModuleMenuCommand()
    {
        Artisan::call('make:module-menu', [
            'name' => $this->name,
            'path' => $this->path
        ]);
    }

    protected function generateRouteNamespaces()
    {
        $routeNamespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"]
        ];

        $routeTemplate = "use {name};";
        $this->routeNamespaceImport = $this->helper->generateDynamicString($routeNamespaceList, $routeTemplate);
    }

    protected function generateRoutes()
    {
        $routes = [
            [
                'uri' => "/{$this->kebabClassName}",
                'action' => "{$this->showComponentName}::class",
                'name' => "{$this->kebabClassName}-show"
            ],
            [
                'uri' => "/{$this->kebabClassName}/cadastro",
                'action' => "{$this->managementComponentName}::class",
                'name' => "{$this->kebabClassName}-create"
            ],
            [
                'uri' => "/{$this->kebabClassName}/{{$this->kebabClassName}}/editar",
                'action' => "{$this->managementComponentName}::class",
                'name' => "{$this->kebabClassName}-edit"
            ]
        ];

        $template = "Route::get('{uri}', {action})->name('{name}');";
        $this->routeDefinition = $this->helper->generateDynamicString($routes, $template);
    }

    protected function callMakeModuleRoutesCommand()
    {
        $this->generateRouteNamespaces();
        $this->generateRoutes();

        Artisan::call('make:module-routes', [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $this->routeNamespaceImport,
            'route-definition' => $this->routeDefinition,
        ]);
    }

    protected function callMakeModuleModelCommand()
    {
        Artisan::call('make:module-model', [
            'name' => $this->name,
            'path' => $this->path
        ]);
    }

    protected function callMakeModuleLivewireComponentCommand()
    {
        Artisan::call('make:module-livewire-route-components', [
            'name' => $this->name,
            'path' => $this->path,
        ]);
    }

    protected function callMakeModuleComposerCommand()
    {
        Artisan::call('make:module-composer', [
            'name' => $this->name,
            'path' => $this->path,
        ]);
    }
}
