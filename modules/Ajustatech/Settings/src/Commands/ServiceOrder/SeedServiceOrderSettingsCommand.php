<?php

namespace Ajustatech\Settings\Commands\ServiceOrder;

use Illuminate\Console\Command;

class SeedServiceOrderSettingsCommand extends Command
{
    protected $signature = 'module:seed-settings';

    protected $description = 'Seed Settings module data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\Settings\Database\Seeders\Company\CompanySettingsSeeder',
        ]);

        $this->call('db:seed', [
            '--class' => 'Ajustatech\Settings\Database\Seeders\ServiceOrder\ServiceOrderSettingsSeeder',
        ]);
    }
}
