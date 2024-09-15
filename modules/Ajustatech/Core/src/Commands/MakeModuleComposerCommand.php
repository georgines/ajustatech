<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleComposerCommand extends BaseCommand
{
    protected $signature = 'make:module-composer {name} {path} {--f|force}';
    protected $description = 'Generate composer file for the specified module';

    protected $helper;
    protected $name;
    protected $path;
    protected $namespace;
    protected $className;
    protected $lowClassName;

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
        $this->info("Composer for module {$this->name} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->className = $this->helper->getClassName($this->name);
        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->lowClassName = $this->helper->getLowClassName($this->className);
    }



    protected function createLivewireStubs()
    {

        $this->helper->addContents([
            "MODULO_TEST_PATH" => "{$this->path}/Tests/",
            "LOW_CLASS_NAME" => $this->lowClassName
        ]);

        $stubs = [
            ['module-composer.stub' => "{$this->path}/composer.json"]
        ];

        $this->helper->createStubFiles($stubs);
    }
}
