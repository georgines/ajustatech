<?php

namespace Ajustatech\Core\Commands\Helpers;

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
        foreach ($stubs as $stub => $file) {
            $this->createFileFromStub($basePath, $className, $stub, $file);
        }
    }

    public function createFileFromStub($basePath, $className, $stub, $file): void
    {
        $stubPath = $this->getStubPath($stub);
        $filePath = "{$basePath}/{$file}";

        $contents = [
            'CLASS_NAME' => $className,
            'NAMESPACE' => $this->namespace,
            'KABAB_CASE_NAME' => $this->getKebabCaseName($className),
            'SNAKE_CASE_NAME' => $this->getSnakeCaseName($className),
            'VENDOR' => $this->config['vendor'],
            'LICENSE' => $this->config['license'],
            'AUTHOR_NAME' => $this->config['author']['name'],
            'AUTHOR_EMAIL' => $this->config['author']['email'],
            'ORGANIZATION' => $this->config['organization'],
        ];

        $contents = $this->getStubContents($stubPath, $contents);

        if (!$this->files->exists($filePath)) {
            $this->files->put($filePath, $contents);
        }
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
}
