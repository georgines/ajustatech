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

    // Modificação no método para incluir a verificação de sobrescrita forçada
    public function createStubFiles(array $stubs, bool $forced = false): void
    {
        foreach ($stubs as $stubArray) {
            foreach ($stubArray as $stub => $file) {
                $this->createFileFromStub($stub, $file, $forced);
            }
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

        // Aqui a verificação para sobrescrever o arquivo se o modo de sobrescrita estiver ativado ou se o parâmetro forçar estiver true
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
        $this->className = $name;
        return $this->className;
    }

    public function getLowClassName($name): string
    {
        $name = $this->getLowCaseName($name);
        return $name;
    }

    public function getKebabClassName($name): string
    {
        $name = $this->getKebabCaseName($name);
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

    public function getLowCaseName($name): string
    {
        $name = Str::lower($name);
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
