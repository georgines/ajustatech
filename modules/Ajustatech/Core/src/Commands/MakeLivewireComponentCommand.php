<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Console\Command;
use Ajustatech\Core\Commands\Helpers\CommandHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MakeLivewireComponentCommand extends Command
{
    protected $signature = 'make:livewire-component {name} {path}';
    protected $description = 'Create a new Livewire 3 component in a specified path';
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
        $path = $this->argument('path');

        $basePath = $this->helper->getBasePath();
        $namespace = $this->helper->getNamespaceFromPath($path);
        $componentName = $this->helper->getClassName($name);
        $kebabComponentName = $this->helper->getKebabCaseName($name);
        $lowComponentName = Str::lower($componentName);
        $showComponentName = "Show" . $componentName;
        $showKebabComponentName = Str::kebab($showComponentName);
        $managementComponentName = $componentName . "Management";
        $managementKebabComponentName = Str::kebab($managementComponentName);
        $timestamp = $this->genarateMigrationTimestamp();

        // dd([
        //     "name" => $name,
        //     "path" => $path,
        //     "basePath" => $basePath,
        //     "namespace" => $namespace,
        //     "componentName" => $componentName,
        //     "kebabComponentName" => $kebabComponentName,
        //     "lowComponentName" => $lowComponentName,
        //     "showComponentName" => $showComponentName,
        //     "showKebabComponentName" => $showKebabComponentName,
        //     "managementComponentName" => $managementComponentName,
        //     "managementKebabComponentName" => $managementKebabComponentName,
        //     "timestamp" => $timestamp,
        // ]);

        $namespaceList = [
             [ 'name'=>"{$namespace}\Livewire\\{$showComponentName}"],
             [ 'name'=>"{$namespace}\Livewire\\{$managementComponentName}"],
             [ 'name'=>"{$namespace}\Commands\Seed{$componentName}Command"],
        ];

        $useTemplate = "use {name};";
        $useNamespace = $this->generateDynamicString($namespaceList, $useTemplate);

        $components = [
            [
                'name' => $showKebabComponentName,
                'class' => "{$showComponentName}::class"
            ],
            [
                'name' => $managementKebabComponentName,
                'class' => "{$managementComponentName}::class"
            ]
        ];

        $template = "\t\tLivewire::component('{name}', {class});";
        $registredComponents = $this->generateDynamicString($components, $template);

        $this->helper->addContents([
            'USE_NAMESPACE' => $useNamespace,
            'REGISTRED_COMPONENTS' => $registredComponents
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'provider.stub' => "{$path}/Providers/{$componentName}ServiceProvider.php",
        ]);

        $this->helper->addContents([
            "LOW_CLASS_NAME" => $lowComponentName
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'menu.stub' => "{$path}/Menu/horizontalMenu.json",
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'menu.stub' => "{$path}/Menu/verticalMenu.json",
        ]);

        $routeNamespaceList = [
           [ 'name'=> "{$namespace}\Livewire\\{$showComponentName}"],
           [ 'name'=> "{$namespace}\Livewire\\{$managementComponentName}"]
        ];

        $routeTemplate = "use {name};";
        $componentRoute = $this->generateDynamicString($routeNamespaceList, $routeTemplate);

        $routes = [
            [
                'uri' => "/{$kebabComponentName}",
                'action' => "{$showComponentName}::class",
                'name' => "{$kebabComponentName}-show"
            ],
            [
                'uri' => "/{$kebabComponentName}/cadastro",
                'action' => "{$managementComponentName}::class",
                'name' => "{$kebabComponentName}-create"
            ],
            [
                'uri' => "/{$kebabComponentName}/{{$kebabComponentName}}/editar",
                'action' => "{$managementComponentName}::class",
                'name' => "{$kebabComponentName}-edit"
            ]
        ];

        $template = "Route::get('{uri}', {action})->name('{name}');";
        $registeredRoutes = $this->generateDynamicString($routes, $template);

        $this->helper->addContents([
            "LOW_CLASS_NAME" => $lowComponentName,
            "KABAB_CASE_NAME" > $kebabComponentName,
            "COMPONENT_ROUTE" => $componentRoute,
            "ROUTE_COMPONENTS" => $registeredRoutes,
            "COMPONENT_NAME" => $showComponentName
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'command.stub' => "{$path}/Commands/Seed{$componentName}Command.php",
            'factory.stub' => "{$path}/Database/Factories/{$componentName}Factory.php",
            'migration.stub' => "{$path}/Database/Migrations/{$timestamp}_create_{$lowComponentName}_table.php",
            'model.stub' => "{$path}/Database/Models/{$componentName}.php",
            'seeder.stub' => "{$path}/Database/Seeders/{$componentName}Seeder.php",
            'routes.stub' => "{$path}/Routes/web.php",
            'test.stub' => "{$path}/Tests/Feature/{$componentName}Test.php"
        ]);

        $this->helper->addContents([
            "LOW_CLASS_NAME" => $lowComponentName,
            "CLASS_NAME" => $componentName,
            "KABAB_CASE_NAME" => $showKebabComponentName,
            "COMPONENT_NAME" => $showComponentName
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'test-component.stub' => "{$path}/Tests/Feature/{$componentName}/{$showComponentName}Test.php",
            'livewire-component.stub' => "{$path}/Livewire/{$showComponentName}.php",
            'livewire-view.stub' => "{$path}/Views/livewire/{$showKebabComponentName}.blade.php"
        ]);

        $this->helper->addContents([
            "LOW_CLASS_NAME" => $lowComponentName,
            "CLASS_NAME" => $componentName,
            "KABAB_CASE_NAME" => $managementKebabComponentName,
            "COMPONENT_NAME" => $managementComponentName
        ]);

        $this->helper->createStubFiles($basePath, $componentName, [
            'test-component.stub' => "{$path}/Tests/Feature/{$componentName}/{$managementComponentName}Test.php",
            'livewire-component.stub' => "{$path}/Livewire/{$managementComponentName}.php",
            'livewire-view.stub' => "{$path}/Views/livewire/{$managementKebabComponentName}.blade.php",
        ]);

        $this->info("Livewire component {$componentName} created successfully at {$basePath}.");
    }

    function generateDynamicString(array $items, string $template): string
    {
        return collect($items)
            ->map(function ($item) use ($template) {
                return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($item) {
                    return $item[$matches[1]] ?? $matches[0];
                }, $template);
            })
            ->implode("\n");
    }

    private function genarateMigrationTimestamp(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }
}
