<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Str;

class MakeModuleRoutesCommand extends BaseCommand
{
    protected $signature = 'make:module-routes {name} {path} {namespace-import} {route-definition} {--f|force}';
    protected $description = 'Generate route stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $namespace;
    protected $className;
    protected $namespaceImport;
    protected $routeDefinition;
    protected $kebabClassName;
    protected $showComponentName;
    protected $managementComponentName;


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
        $this->generateRoutes();

        $this->info("Routes for module {$this->className} created successfully.");
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->namespaceImport = $this->argument('namespace-import');
        $this->routeDefinition = $this->argument('route-definition');

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
    }


    protected function generateRoutes()
    {
        $this->helper->addContents([
            "NAMESPACE_IMPORT" => $this->namespaceImport,
            "ROUTE_DEFINITION" => $this->routeDefinition
        ]);

        $this->helper->createStubFiles([
            ['module-routes.stub' => "{$this->path}/Routes/web.php"]
        ]);
    }
}
