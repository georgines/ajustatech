<?php

namespace Ajustatech\Core\Commands\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Pluralizer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class CommandHelper
{
    protected $files;
    protected $config;
    protected $namespace;
    protected $basePath = '';
    protected $corePath = "modules/Ajustatech";
    protected $newContents = [];
    protected $className;
    protected $forceOverwrite = false;

    public function __construct()
    {
        $this->files = new Filesystem;
        $this->loadConfig();
    }

    public function setForceOverwrite(bool $force): void
    {
        $this->forceOverwrite = $force;
    }

    public function loadConfig(): void
    {
        $path = $this->getBasePath() . $this->corePath . "/Core/src/config/commands.php";

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

    public function getCorePath(): string
    {
        return $this->corePath;
    }

    public function createDirectoryStructure(array $directories): void
    {
        foreach ($directories as $directory) {
            $this->makeDirectory("{$this->basePath}/{$directory}");
        }
    }

    protected function makeDirectory($path): void
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }
    }

    public function createStubFiles(array $stubs, bool $forced = false): void
    {
        foreach ($stubs as $stubArray) {
            foreach ($stubArray as $stub => $file) {
                $this->createFileFromStub($stub, $file, $forced);
            }
        }
    }

    public function findAndDeleteFileIfExists($directory, $pattern)
    {
        $fileName = $this->findFileByPattern($directory, $pattern);

        if ($fileName) {
            $this->deleteFile($directory, $fileName);
        }
    }

    public function findFileByPattern($directory, $pattern)
    {
        if (!$this->files->exists($directory)) {
            return null;
        }

        $files = $this->files->files($directory);
        foreach ($files as $file) {
            if (strpos($file->getFilename(), $pattern) !== false) {
                return $file->getFilename();
            }
        }
        return null;
    }

    public function deleteFile($directory, $fileName)
    {
        $filePath = "{$directory}/{$fileName}";

        if ($this->files->exists($filePath)) {
            $this->files->delete($filePath);
        }
    }

    protected function createFileFromStub($stub, $file, bool $forcedFileOverwrite = false): void
    {
        $stubPath = $this->getStubPath($stub);
        $filePath = "{$this->basePath}/{$file}";

        $directory = dirname($filePath);
        $this->makeDirectory($directory);

        $contents = [
            'CLASS_NAME' => $this->className,
            'NAMESPACE' => $this->namespace,
            "PSR4_NAMESPACE" => $this->getPsr4Namespace(),
            'KABAB_CASE_NAME' => $this->getKebabCaseName($this->className),
            'SNAKE_CASE_NAME' => $this->getSnakeCaseName($this->className),
            'LOW_CLASS_NAME' => $this->getLowCaseName($this->className),
            'VENDOR' => $this->config['vendor'],
            'LICENSE' => $this->config['license'],
            'AUTHOR_NAME' => $this->config['author']['name'],
            'AUTHOR_EMAIL' => $this->config['author']['email'],
            'ORGANIZATION' => $this->config['organization'],
        ];

        $contents = array_merge($contents, $this->newContents);
        $contents = $this->getStubContents($stubPath, $contents);

        if (!$this->files->exists($filePath) || $forcedFileOverwrite || $this->forceOverwrite) {
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

    public function getSingularClassName($name): Stringable
    {
        return Str::of(ucwords(Pluralizer::singular($name)));
    }

    public function getClassName($name): Stringable
    {
        $name = $this->getKebabCaseName($name);
        $name = Str::of($name)
            ->replace(['-', '_'], ' ')
            ->title()
            ->replace(' ', '');
        $this->className = $name;
        return Str::of($this->className);
    }

    public function getLowClassName($name): string
    {
        return $this->getLowCaseName($name);
    }

    public function getKebabClassName($name): string
    {
        return $this->getKebabCaseName($name);
    }

    public function getKebabCaseName($name): string
    {
        return Str::of($name)->ascii()->replace('/[^a-zA-Z0-9]/', '')->kebab();
    }

    public function getSnakeCaseName($name): string
    {
        return Str::of($name)->ascii()->replace('/[^a-zA-Z0-9]/', '')->snake();
    }

    public function getLowCaseName($name): string
    {
        return Str::lower($name);
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

    public function getPsr4NamespaceFromPath($path): string
    {
        return Str::of($this->getNamespaceFromPath($path))->replace("\\", "\\\\") . "\\\\";
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

    public function getModuleNameFromPath(string $modulePath): Stringable
    {
        $cleanedPath = trim($modulePath, '/');
        $modulePart = Str::beforeLast($cleanedPath, '/src');
        $moduleName =  Str::afterLast($modulePart ?: $cleanedPath, '/');
        return Str::of($moduleName);
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

    public function ddcreateStubFiles(array $stubs): void
    {
        $output = [];

        foreach ($stubs as $stubArray) {
            foreach ($stubArray as $stub => $file) {
                $stubPath = $this->getStubPath($stub);
                $filePath = "{$this->basePath}/{$file}";

                $contents = [
                    'CLASS_NAME' => $this->className,
                    'NAMESPACE' => $this->namespace,
                    "PSR4_NAMESPACE" => $this->getPsr4Namespace(),
                    'KABAB_CASE_NAME' => $this->getKebabCaseName($this->className),
                    'SNAKE_CASE_NAME' => $this->getSnakeCaseName($this->className),
                    'LOW_CLASS_NAME' => $this->getLowCaseName($this->className),
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
