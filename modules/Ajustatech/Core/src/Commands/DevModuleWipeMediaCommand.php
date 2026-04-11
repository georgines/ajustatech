<?php

namespace Ajustatech\Core\Commands;

class DevModuleWipeMediaCommand extends BaseCommand
{
    protected $signature = 'module:wipe-media';
    protected $description = 'Run all module media wipe commands';

    public function handle(): int
    {
        if ($this->executeCommand('module:wipe-media-service-order') !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info('All module media wipe commands were executed.');

        return self::SUCCESS;
    }

    private function executeCommand(string $command): int
    {
        $this->info("Running command: {$command}");
        $exitCode = $this->call($command);

        if ($exitCode !== self::SUCCESS) {
            $this->error("Command failed: {$command}");
        }

        return $exitCode;
    }
}
