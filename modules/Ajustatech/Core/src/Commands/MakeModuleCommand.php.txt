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
        $modulePath = "modules/Ajustatech/{$moduleName}";
        $this->helper->getNamespaceFromPath($modulePath);

        // "modules/Ajustatech/Core/src/Tests/"

        $this->helper->addContents(["MODULO_TEST_PATH"=>"{$modulePath}/src/Tests/"]);      

        $this->helper->createDirectoryStructure($basePath, [
            "{$modulePath}/src/Providers",
            "{$modulePath}/src/Routes",
            "{$modulePath}/src/Models",
            "{$modulePath}/src/Tests/Unit",
            "{$modulePath}/src/Tests/Feature",
        ]);

        $this->helper->createStubFiles($basePath, $moduleName, [
            'composer.stub' => "{$modulePath}/composer.json",
            'provider.stub' => "{$modulePath}/src/Providers/{$moduleName}ServiceProvider.php",
            'routes.stub' => "{$modulePath}/src/Routes/web.php",
            'model.stub' => "{$modulePath}/src/Models/{$moduleName}.php"
        ]);

        $this->info("Module {$moduleName} created successfully.");
    }
}
