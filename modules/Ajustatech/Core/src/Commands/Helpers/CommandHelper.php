<?php

namespace Ajustatech\Core\Commands\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CommandHelper
{
    protected $files;
    protected $config;
    protected $namespace;
    protected $basePath = '';
    protected $pathToConfigFile = "modules/Ajustatech/Core/src/config/commands.php";
    protected $newContents = [];

    public function __construct()
    {
        $this->files = new Filesystem;
        $this->loadConfig();
    }

    public function loadConfig(): void
    {
        $path = $this->getBasePath() . $this->pathToConfigFile;

        if ($this->files->exists($path)) {
            $this->config =  $this->files->getRequire($path);
        }
    }

    public function loadConfigFromArray($config): void
    {
        $this->config =  $config;
    }

    public function setBasePath($basePath): void
    {
        $this->basePath = $basePath;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function createDirectoryStructure($basePath, array $directories): void
    {
        foreach ($directories as $directory) {
            $this->makeDirectory("{$basePath}/{$directory}");
        }
    }

    public function createStubFiles($basePath, $className, array $stubs): void
    {
        foreach ($stubs as $stubArray) {
            foreach ($stubArray as $stub => $file) {
                $this->createFileFromStub($basePath, $className, $stub, $file);
            }
        }
    }

    public function createFileFromStub($basePath, $className, $stub, $file): void
    {
        $stubPath = $this->getStubPath($stub);
        $filePath = "{$basePath}/{$file}";

        $directory = dirname($filePath);
        $this->makeDirectory($directory);

        $contents = [
            'CLASS_NAME' => $className,
            'NAMESPACE' => $this->namespace,
            "PSR4_NAMESPACE" => $this->getPsr4Namespace(),
            'KABAB_CASE_NAME' => $this->getKebabCaseName($className),
            'SNAKE_CASE_NAME' => $this->getSnakeCaseName($className),
            'VENDOR' => $this->config['vendor'],
            'LICENSE' => $this->config['license'],
            'AUTHOR_NAME' => $this->config['author']['name'],
            'AUTHOR_EMAIL' => $this->config['author']['email'],
            'ORGANIZATION' => $this->config['organization'],
        ];

        $contents = array_merge($contents, $this->newContents);

        $contents = $this->getStubContents($stubPath, $contents);


        if (!$this->files->exists($filePath)) {
            $this->files->put($filePath, $contents);
        }
    }

    public function addContents($array)
    {
        $this->newContents =  array_merge($this->newContents, $array);
    }

    public function getContents()
    {
        return $this->newContents;
    }

    protected function getStubPath($stub): string
    {
        return __DIR__ . "/../../stubs/{$stub}";
    }

    protected function getStubContents($stub, $variables = []): mixed
    {
        $contents =  $this->files->get($stub);
        foreach ($variables as $search => $replace) {
            $contents = Str::of($contents)->replace('$' . $search . '$', $replace);
        }
        return $contents;
    }

    protected function makeDirectory($path): void
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }

    public function getSingularClassName($name): string
    {
        return ucwords(Pluralizer::singular($name));
    }

    public function getClassName($name): string
    {
        $name = $this->getKebabCaseName($name);
        $name = Str::of($name)
            ->replace(['-', '_'], ' ')
            ->title()
            ->replace(' ', '');
        return $name;
    }

    public function getKebabCaseName($name): string
    {
        $name = Str::of($name)->ascii()->replace('/[^a-zA-Z0-9]/', '')->kebab();
        return $name;
    }

    public function getSnakeCaseName($name): string
    {
        $name = Str::of($name)->ascii()->replace('/[^a-zA-Z0-9]/', '')->snake();
        return $name;
    }

    public function getFolderName($path): string
    {
        $ignore_subdir = $this->config['ignore_subdir'];
        $path = Str::replaceLast("/$ignore_subdir", "", $path);
        return Str::of($path)->trim('/')->explode('/')->last();
    }

    protected function getPsr4Namespace(): string
    {
        return Str::of($this->namespace)->replace("\\", "\\\\") . "\\\\";
    }

    public function getNamespaceFromPath($path): string
    {
        $parts = explode('/', $this->getRelativePath($path));

        if (Str::of(end($parts))->trim('/') == $this->config['ignore_subdir']) {
            array_pop($parts);
        }

        if (isset($parts[0]) && $parts[0] === 'app') {
            $namespaceParts = array_map('ucfirst', array_slice($parts, 1));
            $this->namespace = 'App\\' . implode('\\', $namespaceParts);
            return $this->namespace;
        }

        if (isset($parts[0]) && $parts[0] === 'modules') {
            $namespaceParts = array_map('ucfirst', array_slice($parts, 2));
            $this->namespace = $this->config['organization'] . '\\' . implode('\\', $namespaceParts);
            return $this->namespace;
        }

        $namespaceParts = array_map('ucfirst', $parts);
        $this->namespace = implode('\\', $namespaceParts);
        return $this->namespace;
    }

    private function getRelativePath($path): string
    {
        return str_replace($this->basePath . '/', '', $path);
    }

    public function generateDynamicString(array $items, string $template): string
    {
        return collect($items)
            ->map(function ($item) use ($template) {
                return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($item) {
                    return $item[$matches[1]] ?? $matches[0];
                }, $template);
            })
            ->implode("\n");
    }

    public function generateMigrationTimestamp(): string
    {
        return Carbon::now()->format('Y_m_d_His');
    }

    public function ddcreateStubFiles($basePath, $className, array $stubs): void
    {
        $output = []; // Inicializa um array para coletar as saídas

        foreach ($stubs as $stubArray) {
            foreach ($stubArray as $stub => $file) {
                $stubPath = $this->getStubPath($stub);
                $filePath = "{$basePath}/{$file}";

                $contents = [
                    'CLASS_NAME' => $className,
                    'NAMESPACE' => $this->namespace,
                    "PSR4_NAMESPACE" => $this->getPsr4Namespace(),
                    'KABAB_CASE_NAME' => $this->getKebabCaseName($className),
                    'SNAKE_CASE_NAME' => $this->getSnakeCaseName($className),
                    'VENDOR' => $this->config['vendor'],
                    'LICENSE' => $this->config['license'],
                    'AUTHOR_NAME' => $this->config['author']['name'],
                    'AUTHOR_EMAIL' => $this->config['author']['email'],
                    'ORGANIZATION' => $this->config['organization'],
                ];

                $contents = array_merge($contents, $this->newContents);
                $contents = $this->getStubContents($stubPath, $contents);

                $output[] = [
                    'filePath' => $filePath,
                    'contents' => $contents
                ];
            }
        }

        dd($output);
    }
}
