<?php

namespace Ajustatech\Core\Commands;

use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Str;

class MakeModuleLivewireComponentCommand extends BaseCommand
{
    protected $signature = 'make:module-livewire-route-components {name} {path} {--f|force}';
    protected $description = 'Generate Livewire routes components for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $kebabModuleName;
    protected $className;
    protected $kebabClassName;
    protected $namespace;
    protected $showComponentName;
    protected $showKebabComponentName;
    protected $managementComponentName;
    protected $managementKebabComponentName;

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
        $this->createLivewireStubs();
        $this->showInstructions();
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');


        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->className = $this->helper->getClassName($this->name);
        $this->kebabClassName = $this->helper->getClassName($this->name)->kebab();
        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->kebabModuleName = $this->helper->getModuleNameFromPath($this->path)->kebab();
        $this->showComponentName = "Show" . $this->className;
        $this->showKebabComponentName = $this->helper->getKebabCaseName($this->showComponentName);
        $this->managementComponentName = $this->className . "Management";
        $this->managementKebabComponentName = $this->helper->getKebabCaseName($this->managementComponentName);
    }

    protected function createLivewireStubs()
    {
        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->showKebabComponentName,
            "COMPONENT_NAME" => $this->showComponentName,
            "KABAB_MODULE_NAME" => $this->kebabModuleName
        ]);

        $stubs = [
            ['module-test-component.stub' => "{$this->path}/Tests/Feature/{$this->className}/{$this->showComponentName}Test.php"],
            ['module-livewire-component.stub' => "{$this->path}/Livewire/{$this->showComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->path}/Views/livewire/{$this->showKebabComponentName}.blade.php"]
        ];

        $this->helper->createStubFiles($stubs);

        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->managementKebabComponentName,
            "COMPONENT_NAME" => $this->managementComponentName,
            "KABAB_MODULE_NAME" => $this->kebabModuleName
        ]);

        $stubs = [
            ['module-test-component.stub' => "{$this->path}/Tests/Feature/{$this->className}/{$this->managementComponentName}Test.php"],
            ['module-livewire-component.stub' => "{$this->path}/Livewire/{$this->managementComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->path}/Views/livewire/{$this->managementKebabComponentName}.blade.php"],
        ];

        $this->helper->createStubFiles($stubs);
    }

    protected function showInstructions()
    {
        $this->line('');
        $this->componentInstructions();
        $this->routesInstructions();
        $this->info("🔥 Livewire route components for module {$this->name} created successfully.");
    }

    protected function componentInstructions()
    {
        $components = [
            ['Alias' => $this->showKebabComponentName, 'Class' => $this->showComponentName],
            ['Alias' => $this->managementKebabComponentName, 'Class' => $this->managementComponentName],
        ];

        $this->displayMessage('You can register the following Livewire components in your service provider:', 'yellow');

        $this->table(
            ['Alias', 'Class'],
            $components
        );
        $this->line('');
        $this->displayMessage("Livewire::component('{$this->showKebabComponentName}', {$this->showComponentName}::class);",  'magenta');
        $this->displayMessage("Livewire::component('{$this->managementKebabComponentName}', {$this->managementComponentName}::class);", 'magenta');
        $this->line('');
    }

    protected function routesInstructions()
    {
        $this->displayMessage("📚 You can register the following routes in your routes file:", 'yellow');
        $this->line('');

        $this->displayMessage("use Illuminate\Support\Facades\Route;", 'blue');
        $this->displayMessage("use {$this->namespace}\\Livewire\\{$this->showComponentName};", 'blue');
        $this->displayMessage("use {$this->namespace}\\Livewire\\{$this->managementComponentName};", 'blue');
        $this->line('');

        $this->displayMessage("Route::get('/uri', {$this->showComponentName}::class)->name('{$this->kebabClassName}-show');", 'magenta');
        $this->displayMessage("Route::get('/uri/cadastro', {$this->managementComponentName}::class)->name('{$this->kebabClassName}-create');", 'magenta');
        $this->displayMessage("Route::get('/uri/{id}/editar', {$this->managementComponentName}::class)->name('{$this->kebabClassName}-edit');", 'magenta');
        $this->line('');
    }
}
