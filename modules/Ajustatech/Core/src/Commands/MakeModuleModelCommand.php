<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Support\Str;

class MakeModuleModelCommand extends Command
{
    protected $signature = 'make:module-model {name} {path}';
    protected $description = 'Generate the model and migration stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $lowClassName;
    protected $className;
    protected $timestamp;
    protected $pluralTable;
    protected $namespace;
    protected $basePath;
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
        $this->path = $this->argument('path');

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->timestamp = $this->helper->generateMigrationTimestamp();
        $this->pluralTable = Str::plural($this->lowClassName);

        $this->helper->addContents([
            'PLURAL_TABLE' => $this->pluralTable
        ]);

        $this->generateModelAndMigrationStubs();

        $this->info("Model and migration stubs for module {$this->name} created successfully at {$this->path}.");
    }

    protected function generateModelAndMigrationStubs()
    {
        $stubs = [
            ['module-factory.stub' => "{$this->path}/Database/Factories/{$this->className}Factory.php"],
            ['module-migration.stub' => "{$this->path}/Database/Migrations/{$this->timestamp}_create_{$this->lowClassName}_table.php"],
            ['module-model.stub' => "{$this->path}/Database/Models/{$this->className}.php"],
            ['module-seeder.stub' => "{$this->path}/Database/Seeders/{$this->className}Seeder.php"],
            ['module-test-model.stub' => "{$this->path}/Tests/Feature/{$this->className}Test.php"],
            ['module-command.stub' => "{$this->path}/Commands/Seed{$this->className}Command.php"]
        ];
        $this->helper->createStubFiles($stubs);
    }
}
