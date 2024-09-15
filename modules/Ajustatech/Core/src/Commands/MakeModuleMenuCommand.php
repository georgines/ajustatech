<?php

namespace Ajustatech\Core\Commands;

use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleMenuCommand extends BaseCommand
{
    protected $signature = 'make:module-menu {name} {path} {--f|force}';
    protected $description = 'Generate menu stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $kebabModuleName;
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

        $this->kebabModuleName = $this->helper->getModuleNameFromPath($this->path)->kebab();

        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->timestamp = $this->helper->generateMigrationTimestamp();

        $this->generateMenuStubs();
        $this->showComponentInstructions();
    }

    protected function generateMenuStubs()
    {
        $this->helper->addContents([
            "LOW_CLASS_NAME" => $this->lowClassName,
            "KABAB_MODULE_NAME" => $this->kebabModuleName
        ]);

        $stub =  [
            ['module-menu.stub' => "{$this->path}/Menu/horizontalMenu.json"],
            ['module-menu.stub' => "{$this->path}/Menu/verticalMenu.json"],
        ];

        $this->helper->createStubFiles($stub);
    }

    protected function showComponentInstructions()
    {
        $this->info("🔥 Menu stubs for module {$this->name} created successfully at {$this->path}.");
    }
}
