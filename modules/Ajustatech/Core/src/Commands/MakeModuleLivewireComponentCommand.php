<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleLivewireComponentCommand extends Command
{
    protected $signature = 'make:module-livewire-component {name} {path}';
    protected $description = 'Generate Livewire components for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
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
        $this->info("Livewire components for module {$this->name} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->showComponentName = "Show" . $this->helper->getClassName($this->name);
        $this->showKebabComponentName = $this->helper->getKebabCaseName($this->showComponentName);
        $this->managementComponentName = $this->helper->getClassName($this->name) . "Management";
        $this->managementKebabComponentName = $this->helper->getKebabCaseName($this->managementComponentName);
    }



    protected function createLivewireStubs()
    {
        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->showKebabComponentName,
            "COMPONENT_NAME" => $this->showComponentName
        ]);

        $stubs = [
            ['module-test-component.stub' => "{$this->path}/Tests/Feature/{$this->showComponentName}Test.php"],
            ['module-livewire-component.stub' => "{$this->path}/Livewire/{$this->showComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->path}/Views/livewire/{$this->showKebabComponentName}.blade.php"]
        ];

        $this->helper->createStubFiles($stubs);

        $this->helper->addContents([
            "KABAB_CASE_NAME" => $this->managementKebabComponentName,
            "COMPONENT_NAME" => $this->managementComponentName
        ]);

        $stubs = [
            ['module-test-component.stub' => "{$this->path}/Tests/Feature/{$this->managementComponentName}Test.php"],
            ['module-livewire-component.stub' => "{$this->path}/Livewire/{$this->managementComponentName}.php"],
            ['module-livewire-view.stub' => "{$this->path}/Views/livewire/{$this->managementKebabComponentName}.blade.php"],
        ];

        $this->helper->createStubFiles($stubs);
    }
}
