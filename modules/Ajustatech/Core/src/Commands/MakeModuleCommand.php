<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = "Create a new module with necessary files and directories";
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
        $moduleName = $this->helper->getClassName($name);
        $basePath = $this->helper->getBasePath();
        $path = "modules/Ajustatech/{$moduleName}";
        $this->helper->getNamespaceFromPath($path);

        $this->helper->createDirectoryStructure($basePath, [
            "{$path}/src/Providers",
            "{$path}/src/Routes",
            "{$path}/src/Models",
            "{$path}/src/Http/Livewire",
        ]);

        $this->helper->createStubFiles($basePath, $moduleName, [
            'composer.stub' => "{$path}/composer.json",
            'provider.stub' => "{$path}/src/Providers/{$moduleName}ServiceProvider.php",
            'routes.stub' => "{$path}/src/Routes/web.php",
            'model.stub' => "{$path}/src/Models/{$moduleName}.php"
        ]);

        $this->info("Module {$moduleName} created successfully.");
    }
}
