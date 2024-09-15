<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevModuleSeedCommand extends BaseCommand
{
    protected $signature = 'module:seed';
    protected $description = 'Seed specific modules';

    public function handle()
    {

        $commands = Artisan::all();
        $filteredCommands = array_filter(array_keys($commands), function ($command) {
            return strpos($command, 'module:seed') === 0 && $command !== 'module:seed';
        });

        if (empty($filteredCommands)) {
            $this->error("No commands found with the prefix 'module:seed'.");
            return;
        }

        foreach ($filteredCommands as $command) {
            $this->info("Running command: {$command}");
            Artisan::call($command, [], $this->getOutput());
        }

        $this->info("🔥 All commands with the prefix 'module:seed' have been executed.");
    }
}
