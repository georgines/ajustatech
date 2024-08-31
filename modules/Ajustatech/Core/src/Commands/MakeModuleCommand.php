<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

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
        $lowModuleName = Str::lower($name);
        $this->helper->addContents(["MODULO_TEST_PATH" => "{$modulePath}/src/Tests/"]);
        $this->helper->addContents(["LOW_CLASS_NAME" => $lowModuleName]);

        $this->helper->createDirectoryStructure($basePath, [
            "{$modulePath}/src/Commands",
            "{$modulePath}/src/Database/Factories",
            "{$modulePath}/src/Database/Migrations",
            "{$modulePath}/src/Database/Models",
            "{$modulePath}/src/Database/Seeders",
            "{$modulePath}/src/Livewire",
            "{$modulePath}/src/Menu",
            "{$modulePath}/src/Providers",
            "{$modulePath}/src/Routes",
            "{$modulePath}/src/Tests/Unit",
            "{$modulePath}/src/Tests/Feature/{$moduleName}",
            "{$modulePath}/src/Views/livewire",

        ]);

        $timestamp = $this->genarateMigrationTimestamp();

        $this->helper->createStubFiles($basePath, $moduleName, [
            // 'command.stub' => "{$modulePath}/src/Commands/Seed{$moduleName}Command.php",
            // 'factory.stub' => "{$modulePath}/src/Database/Factories/{$moduleName}Factory.php",
            // 'migration.stub' => "{$modulePath}/src/Database/Migrations/{$timestamp}_create_{$lowModuleName}_table.php",
            // 'model.stub' => "{$modulePath}/src/Database/Models/{$moduleName}.php",
            // 'seeder.stub' => "{$modulePath}/src/Database/Seeders/{$moduleName}Seeder.php",
            // 'routes-component.stub' => "{$modulePath}/src/Routes/web.php",
            // 'test-component.stub' => "{$modulePath}/src/Tests/Feature/CustomerTest.php",
            ['module-composer.stub' => "{$modulePath}/composer.json"]

        ]);

        Artisan::call('make:livewire-component', [
            'name' => "{$moduleName}",
            'path' => "{$modulePath}/src"
        ]);

        $this->info("Module {$moduleName} created successfully.");
    }

    private function genarateMigrationTimestamp(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }
}
