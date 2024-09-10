<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Facades\Artisan;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = "Create a new module with necessary files and directories";
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
        $this->createDirectoriesWithGitkeep();
        $this->makeLangFiles();

        $this->info("Module {$this->className} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');

        $this->className = $this->helper->getClassName($this->name);
        $this->path = "modules/Ajustatech/{$this->className}/src";

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->showComponentName = "Show" . $this->className;
        $this->showKebabComponentName = $this->helper->getKebabCaseName($this->showComponentName);
        $this->managementComponentName = $this->className . "Management";
        $this->managementKebabComponentName = $this->helper->getKebabCaseName($this->managementComponentName);
        $this->timestamp = $this->helper->generateMigrationTimestamp();
    }

    protected function generateUseNamespaces(): string
    {
        $namespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"],
            ['name' => "{$this->namespace}\Commands\Seed{$this->className}Command"],
        ];

        $useTemplate = "use {name};";
        $providerNamespaceImport = $this->helper->generateDynamicString($namespaceList, $useTemplate);
        return $providerNamespaceImport;
    }

    protected function generateComponentRegistration(): string
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
        $componentRegister = $this->helper->generateDynamicString($components, $template);

        $this->helper->addContents(['REGISTRED_COMPONENTS' => $componentRegister]);

        return $componentRegister;
    }

    protected function callMakeModuleProviderCommand()
    {
        $providerNamespaceImport = $this->generateUseNamespaces();
        $componentRegister = $this->generateComponentRegistration();

        Artisan::call('make:module-provider', [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $providerNamespaceImport,
            'component-register' => $componentRegister
        ]);
    }

    protected function callMakeModuleMenuCommand()
    {
        Artisan::call('make:module-menu', [
            'name' => $this->name,
            'path' => $this->path
        ]);
    }

    protected function generateRouteNamespaces(): string
    {
        $routeNamespaceList = [
            ['name' => "{$this->namespace}\Livewire\\{$this->showComponentName}"],
            ['name' => "{$this->namespace}\Livewire\\{$this->managementComponentName}"]
        ];

        $routeTemplate = "use {name};";
        $routeNamespaceImport = $this->helper->generateDynamicString($routeNamespaceList, $routeTemplate);
        return $routeNamespaceImport;
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
        $routeDefinition = $this->helper->generateDynamicString($routes, $template);
        return $routeDefinition;
    }

    protected function callMakeModuleRoutesCommand(): void
    {
        $routeNamespaceImport = $this->generateRouteNamespaces();
        $routeDefinition = $this->generateRoutes();

        Artisan::call('make:module-routes', [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $routeNamespaceImport,
            'route-definition' => $routeDefinition,
        ]);
    }

    protected function callMakeModuleModelCommand(): void
    {
        Artisan::call('make:module-model', [
            'name' => $this->name,
            'path' => $this->path
        ]);
    }

    protected function callMakeModuleLivewireComponentCommand(): void
    {
        Artisan::call('make:module-livewire-route-components', [
            'name' => $this->name,
            'path' => $this->path,
        ]);
    }

    protected function callMakeModuleComposerCommand(): void
    {
        Artisan::call('make:module-composer', [
            'name' => $this->name,
            'path' => $this->path,
        ]);
    }

    protected function createDirectoriesWithGitkeep(): void
    {
        $stubs = [
            ['gitkeep.stub' => "{$this->path}/Tests/Unit/.gitkeep"],
            ['gitkeep.stub' => "{$this->path}/Tests/Feature/.gitkeep"]
        ];
        $this->helper->createStubFiles($stubs);
    }

    protected function makeLangFiles(): void
    {
        $stubs = [
            ['module-lang-en.stub' => "{$this->path}/Lang/en/messages.php"],
            ['module-lang-pt-br.stub' => "{$this->path}/Lang/pt-BR/messages.php"]
        ];
        $this->helper->createStubFiles($stubs);
    }
}
