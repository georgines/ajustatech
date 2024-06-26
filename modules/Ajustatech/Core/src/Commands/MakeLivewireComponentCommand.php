<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeLivewireComponentCommand extends Command
{
    protected $signature = 'make:livewire-component {name} {path}';
    protected $description = 'Create a new Livewire 3 component in a specified path';
    protected $helper;

    public function __construct(CommandHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->helper->setBasePath(base_path());
        $this->helper->loadConfig();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->argument('path');

        $componentName = $this->helper->getClassName($name);

        $basePath = $this->helper->getBasePath();

        $this->helper->getNamespaceFromPath($path);

        $this->helper->createDirectoryStructure($basePath, [
            "{$path}/Http/Livewire",
            "{$path}/Views/livewire",
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'livewire-component.stub' => "{$path}/Http/Livewire/{$componentName}.php",
            'livewire-view.stub' => "{$path}/Views/livewire/{$this->helper->getKebabCaseName($componentName)}.blade.php",
        ]);

        $this->info("Livewire component {$componentName} created successfully at {$basePath}.");
    }
}
