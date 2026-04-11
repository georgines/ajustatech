<?php

namespace Ajustatech\ServiceOrder\Commands\Analysis;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Throwable;

class WipeAnalysisMediaCommand extends Command
{
    protected $signature = 'feature:wipe-media-service-order-analysis';
    protected $description = 'Wipe Analysis feature media directories on configured local and remote disks';

    public function handle(): int
    {
        $directories = collect((array) config('media_wipe.modules.service-order.analysis.directories', []))
            ->map(fn (string $directory) => trim($directory, '/'))
            ->filter(fn (string $directory) => $directory !== '')
            ->values()
            ->all();

        if (empty($directories)) {
            $this->warn('No media directories configured for Service Order Analysis feature.');

            return self::SUCCESS;
        }

        $diskNames = array_keys((array) config('filesystems.disks', []));
        $hasFailures = false;

        foreach ($diskNames as $diskName) {
            try {
                $disk = Storage::disk($diskName);

                foreach ($directories as $directory) {
                    $disk->deleteDirectory($directory);
                }

                $this->restorePublicDiskGitignore($diskName);
                $this->info("Service Order Analysis media cleaned on disk '{$diskName}'.");
            } catch (Throwable $exception) {
                $hasFailures = true;
                $this->error("Failed to wipe Service Order Analysis media on disk '{$diskName}': {$exception->getMessage()}");
            }
        }

        return $hasFailures ? self::FAILURE : self::SUCCESS;
    }

    private function restorePublicDiskGitignore(string $diskName): void
    {
        if ($diskName !== 'public') {
            return;
        }

        $rootPath = (string) config('filesystems.disks.public.root');
        if ($rootPath === '' || !is_dir($rootPath)) {
            return;
        }

        $filesystem = new Filesystem();
        $gitignorePath = $rootPath . DIRECTORY_SEPARATOR . '.gitignore';

        if ($filesystem->exists($gitignorePath)) {
            return;
        }

        $filesystem->put($gitignorePath, "*\n!.gitignore\n");
    }
}

