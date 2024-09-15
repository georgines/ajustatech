<?php

namespace Ajustatech\Core\Commands;

use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Facades\Artisan;

class MakeModuleCommand extends BaseCommand
{
    protected $signature = 'make:module {name} {--f|force}';
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
    protected $force;

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
        $this->showInstructions();
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');

        $this->force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($this->force);

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

        $parameters = [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $providerNamespaceImport,
            'component-register' => $componentRegister
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-provider', $parameters);
    }

    protected function callMakeModuleMenuCommand()
    {
        $parameters = [
            'name' => $this->name,
            'path' => $this->path
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-menu', $parameters);
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

        $parameters = [
            'name' => $this->name,
            'path' => $this->path,
            'namespace-import' => $routeNamespaceImport,
            'route-definition' => $routeDefinition,
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-routes', $parameters);
    }

    protected function callMakeModuleModelCommand(): void
    {
        $parameters = [
            'name' => $this->name,
            'path' => $this->path
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-model', $parameters);
    }

    protected function callMakeModuleLivewireComponentCommand(): void
    {
        $parameters = [
            'name' => $this->name,
            'path' => $this->path,
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-livewire-route-components', $parameters);
    }

    protected function callMakeModuleComposerCommand(): void
    {
        $parameters = [
            'name' => $this->name,
            'path' => $this->path,
        ];

        $parameters = $this->addForceOption($parameters);
        Artisan::call('make:module-composer', $parameters);
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

    protected function addForceOption(array $parameters): array
    {
        $optionalParam =  $this->force ? ['--force' => true] : [];
        return array_merge($parameters, $optionalParam);
    }

    protected function showInstructions()
    {
        $this->info("🔥 Module {$this->className} created successfully.");

        $this->checkList();
        $this->serviceProviderRegisterInstructions();
        $this->composerInstructions();
        $this->testRegisterInstructions();
        $this->migrateCommandInstructions();
        $this->line('');
    }

    protected function checkList()
    {
        $this->line('');
        $this->displayMessage("📋 Checklist to finalize your module setup:", 'yellow');
        $this->line('');
        $this->displayMessage("✅ 1. Register the service provider in the CoreServiceProvider.", 'blue');
        $this->displayMessage("✅ 2. Add the module's namespace to composer.json.", 'blue');
        $this->displayMessage("✅ 3. Register the Livewire components in the service provider.", 'blue');
        $this->displayMessage("✅ 4. Define your routes in the module's route file.", 'blue');
        $this->displayMessage("✅ 5. Make sure to create any necessary language files.", 'blue');
        $this->displayMessage("✅ 6. Run the development migrations using the following command:", 'blue');
        $this->line('');
    }

    protected function composerInstructions()
    {
        $this->line('');
        $this->displayMessage("📦 To register the module in composer.json, follow the steps below:", 'yellow');
        $this->line('');
        $this->displayMessage("1. Open your composer.json file.", 'blue');
        $this->displayMessage("2. Under the 'autoload' section, add the following line:", 'blue');
        $this->displayMessage("\t\"{$this->helper->getPsr4NamespaceFromPath($this->path)}\": \"{$this->path}\"", 'magenta');
        $this->line('');
        $this->displayMessage("3. Run `composer dumpautoload` to update the autoloader.", 'green');
        $this->line('');
    }

    protected function serviceProviderRegisterInstructions()
    {
        $this->line('');
        $this->displayMessage("🔧 To register the module's service provider in CoreServiceProvider:", 'yellow');
        $this->line('');
        $this->displayMessage("1. Open `CoreServiceProvider.php` in the `modules/Ajustatech/Core/src/Providers` directory.", 'blue');
        $this->displayMessage("2. At the top of the file, add the following line:", 'blue');
        $this->displayMessage("\tuse {$this->namespace}\\Providers\\{$this->className}ServiceProvider;", 'magenta');
        $this->line('');
        $this->displayMessage("3. In the `register()` method, place the following line **between the penultimate and the last provider**:", 'blue');
        $this->displayMessage("\t\$this->app->register({$this->className}ServiceProvider::class);", 'magenta');
        $this->line('');
        $this->displayMessage("4. Ensure that the last provider remains `ViewServiceProvider`.", 'blue');
        $this->line('');
    }

    protected function testRegisterInstructions()
    {
        $this->line('');
        $this->displayMessage("🧪 To register the test directories in phpunit.xml:", 'yellow');
        $this->line('');
        $this->displayMessage("1. Open your `phpunit.xml` file.", 'blue');
        $this->displayMessage("2. Add the following lines in the `<testsuite>` section:", 'blue');
        $this->displayMessage("\t<directory>{$this->path}/Tests/Unit</directory>", 'magenta');
        $this->displayMessage("\t<directory>{$this->path}/Tests/Feature</directory>", 'magenta');
        $this->line('');
    }

    protected function migrateCommandInstructions()
    {
        $this->line('');
        $this->displayMessage("🛠️ To run development migrations, use the following command:", 'yellow');
        $this->line('');
        $this->displayMessage("php artisan dev:migrate", 'magenta');
        $this->line('');
        $this->displayMessage("This will run the migrations for the development environment.", 'blue');
        $this->line('');
    }
}
