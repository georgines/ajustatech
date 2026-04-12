<?php

namespace Ajustatech\Settings\Commands\CompanyHours;

use Illuminate\Console\Command;

class SeedCompanyHoursCommand extends Command
{
    protected $signature = 'module:seed-company-hours';

    protected $description = 'Seed company hours settings';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Settings\\Database\\Seeders\\CompanyHours\\CompanyHoursSeeder',
        ]);
    }
}
