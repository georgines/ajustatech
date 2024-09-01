<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = "Create a new module with necessary files and directories";
    protected $helper;
    protected $name;
    protected $path;
    protected $lowClassName;
    protected $className;
    protected $namespace;
    protected $kebabClassName;

    public function __construct(CommandHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->helper->setBasePath(base_path());
        $this->helper->loadConfig();
    }

    public function handle()
    {
        $this->name = $this->argument('name');

        $this->className = $this->helper->getClassName($this->name);
        $this->path = "modules/Ajustatech/{$this->className}";

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        
        $this->helper->addContents(["MODULO_TEST_PATH" => "{$this->path}/src/Tests/"]);
        $this->helper->addContents(["LOW_CLASS_NAME" => $this->lowClassName]);

        $this->helper->createDirectoryStructure([
            "{$this->path}/src/Commands",
            "{$this->path}/src/Database/Factories",
            "{$this->path}/src/Database/Migrations",
            "{$this->path}/src/Database/Models",
            "{$this->path}/src/Database/Seeders",
            "{$this->path}/src/Livewire",
            "{$this->path}/src/Menu",
            "{$this->path}/src/Providers",
            "{$this->path}/src/Routes",
            "{$this->path}/src/Tests/Unit",
            "{$this->path}/src/Tests/Feature/{$this->className}",
            "{$this->path}/src/Views/livewire",
        ]);

        $this->helper->createStubFiles([
            ['module-composer.stub' => "{$this->path}/composer.json"]
        ]);

        Artisan::call('make:livewire-component', [
            'name' => "{$this->className}",
            'path' => "{$this->path}/src"
        ]);

        $this->info("Module {$this->className} created successfully.");
    }
}
