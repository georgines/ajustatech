<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevMigrateCommand extends BaseCommand
{
    protected $signature = 'dev:migrate';
    protected $description = 'Run migrations';

    public function handle()
    {
        Artisan::call("migrate:fresh", [], $this->getOutput());
        $this->info("🔥 Migrations completed successfully!");
    }
}
