<?php

namespace Ajustatech\Core\Commands;

use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleModelCommand extends BaseCommand
{
    protected $signature = 'make:module-model {name} {path} {--f|force}';
    protected $description = 'Generate the model and migration stubs for the specified module component';

    protected $helper;
    protected $name;
    protected $path;
    protected $lowClassName;
    protected $className;
    protected $timestamp;
    protected $pluralTable;
    protected $moduloName;
    protected $namespace;
    protected $kebabClassName;
    protected $filesystem;
    protected $migrationDirectory;
    protected $migrationFileNamePattern;

    public function __construct(CommandHelper $helper, Filesystem $filesystem)
    {
        parent::__construct();
        $this->helper = $helper;
        $this->helper->setBasePath(base_path());
        $this->helper->loadConfig();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $this->initializeProperties();
        $this->generateModelAndMigrationStubs();
        $this->showInstructions();
    }

    protected function initializeProperties()
    {
        $this->name = $this->argument('name');
        $this->path = $this->argument('path');

        $this->moduloName = $this->helper->getModuleNameFromPath($this->path);

        $force = $this->option('force') ? true : false;
        $this->helper->setForceOverwrite($force);

        $this->namespace = $this->helper->getNamespaceFromPath($this->path);
        $this->className = $this->helper->getClassName($this->name);
        $this->lowClassName = $this->helper->getLowClassName($this->name);
        $this->kebabClassName = $this->helper->getKebabClassName($this->name);
        $this->timestamp = $this->helper->generateMigrationTimestamp();
        $this->pluralTable = Str::plural($this->helper->getSnakeCaseName($this->className));
        $this->migrationDirectory = "{$this->path}/Database/Migrations";
        $this->migrationFileNamePattern = "_create_{$this->pluralTable}_table.php";

        $this->helper->addContents([
            'PLURAL_TABLE' => $this->pluralTable
        ]);
    }

    protected function generateModelAndMigrationStubs()
    {
        $this->handleExistingMigrationFile();
        $stubs = $this->getStubFiles();

        $this->helper->createStubFiles($stubs);
    }

    protected function handleExistingMigrationFile()
    {
        $this->helper->findAndDeleteFileIfExists($this->migrationDirectory, $this->migrationFileNamePattern);
    }

    protected function getStubFiles()
    {
        return [
            ['module-factory.stub' => "{$this->path}/Database/Factories/{$this->className}Factory.php"],
            ['module-migration.stub' => "{$this->migrationDirectory}/{$this->timestamp}{$this->migrationFileNamePattern}"],
            ['module-model.stub' => "{$this->path}/Database/Models/{$this->className}.php"],
            ['module-seeder.stub' => "{$this->path}/Database/Seeders/{$this->className}Seeder.php"],
            ['module-test-model.stub' => "{$this->path}/Tests/Feature/{$this->className}Test.php"],
            ['module-seed-command.stub' => "{$this->path}/Commands/Seed{$this->className}Command.php"]
        ];
    }

    protected function showInstructions()
    {
        $this->line('');
        $this->commandRegistrationInstructions();
        $this->migrateCommandInstructions();
        $this->seedCommandInstructions();
        $this->line('');
        $this->info("🔥 Model and migration stubs for the module '{$this->name}' have been successfully created at {$this->path}.");
    }

    protected function migrateCommandInstructions()
    {
        $this->line('');
        $this->displayMessage("🛠️ To run development migrations, use the following command:", 'yellow');
        $this->line('');
        $this->displayMessage("php artisan dev:migrate", 'magenta');
        $this->line('');
        $this->displayMessage("This will execute the migrations in the development environment.", 'blue');
    }

    protected function seedCommandInstructions()
    {
        $this->line('');
        $this->displayMessage("🛠️ To run development seeds, use the following command:", 'yellow');
        $this->line('');
        $this->displayMessage("php artisan dev:seed", 'magenta');
        $this->line('');
        $this->displayMessage("This will execute the seeders for the development environment.", 'blue');
    }

    protected function commandRegistrationInstructions()
    {
        $this->line('');
        $this->displayMessage("📝 To register your commands in the CommandServiceProvider:", 'yellow');
        $this->line('');
        $this->displayMessage("1. Open the `{$this->moduloName}ServiceProvider.php` file in the \"{$this->path}/Providers\" directory.", 'blue');
        $this->displayMessage("2. Add the following `use` statement at the top of the file:", "blue");
        $this->line('');
        $this->displayMessage("use {$this->namespace}\\Commands\\Seed{$this->className}Command;", 'magenta');
        $this->line('');
        $this->displayMessage("3. In the `loadCommands()` method, add the following line to register your command:", 'blue');
        $this->displayMessage("4. The `loadCommands()` method should look like this:", 'blue');
        $this->line('');
        $this->displayMessage("private function loadCommands()", 'blue');
        $this->displayMessage("{", 'blue');
        $this->displayMessage("\t\$this->commands([", 'blue');
        $this->displayMessage("\t\t// Other commands", 'blue');
        $this->displayMessage("\t\tSeed{$this->className}Command::class,", 'magenta');
        $this->displayMessage("\t]);", 'blue');
        $this->displayMessage("}", 'blue');
        $this->line('');
    }
}
