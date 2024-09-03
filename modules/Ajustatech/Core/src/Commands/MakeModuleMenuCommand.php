<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleMenuCommand extends Command
{
    protected $signature = 'make:module-menu {name} {path}';
    protected $description = 'Generate menu stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $namespace;
    protected $className;
    protected $lowClassName;
    protected $kebabClassName;
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
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');       

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->timestamp = $this->helper->generateMigrationTimestamp();

        $this->generateMenuStubs();

        $this->info("Menu stubs for module {$this->name} created successfully at {$this->path}.");
    }

    protected function generateMenuStubs()
    {
        $this->helper->addContents(["LOW_CLASS_NAME" => $this->lowClassName]);

        $stub =  [
            ['module-menu.stub' => "{$this->path}/Menu/horizontalMenu.json"],
            ['module-menu.stub' => "{$this->path}/Menu/verticalMenu.json"],
        ];

        $this->helper->createStubFiles($stub);
    }
}