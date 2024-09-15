<?php

namespace Ajustatech\Core\Commands;

use Ajustatech\Core\Commands\Helpers\CommandHelper;

class MakeModuleProviderCommand extends BaseCommand
{
    protected $signature = 'make:module-provider {name} {path} {namespace-import} {component-register} {--f|force}';
    protected $description = 'Generate provider stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $className;
    protected $namespaceImport;
    protected $componentRegister;


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

        $this->createProviderStub();

        $this->info("Provider for module {$this->className} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->helper->getNamespaceFromPath($this->path);
        $this->namespaceImport = $this->argument('namespace-import');
        $this->componentRegister = $this->argument('component-register');
        $this->className = $this->helper->getClassName($this->name);
    }

    protected function createProviderStub()
    {
        $this->helper->addContents([
            'REGISTRED_COMPONENTS' => $this->componentRegister,
            'NAMESPACE_IMPORT' => $this->namespaceImport,
        ]);

        $stubs = [
            ['module-provider.stub' => "{$this->path}/Providers/{$this->className}ServiceProvider.php"],
        ];

        $this->helper->createStubFiles($stubs);
    }
}
