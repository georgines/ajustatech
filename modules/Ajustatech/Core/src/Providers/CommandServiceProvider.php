<?php

namespace Ajustatech\Core\Providers;

use Ajustatech\Core\Commands\DevClearCachesCommand;
use Ajustatech\Core\Commands\DevMigrateCommand;
use Ajustatech\Core\Commands\DevModuleWipeMediaCommand;
use Ajustatech\Core\Commands\DevModuleSeedCommand;
use Ajustatech\Core\Commands\DevReinstallCommand;
use Ajustatech\Core\Commands\DevSeedCommand;
use Ajustatech\Core\Commands\DevWipeMediaCommand;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Commands\MakeModuleCommand;
use Ajustatech\Core\Commands\MakeModuleMenuCommand;
use Ajustatech\Core\Commands\MakeModuleModelCommand;
use Ajustatech\Core\Commands\MakeModuleRoutesCommand;
use Ajustatech\Core\Commands\MakeModuleProviderCommand;
use Ajustatech\Core\Commands\MakeModuleLivewireComponentCommand;
use Ajustatech\Core\Commands\MakeModuleComposerCommand;


class CommandServiceProvider extends ServiceProvider
{

    public function register(): void {}

    public function boot(): void
    {

        $this->commands([
            DevClearCachesCommand::class,
            DevMigrateCommand::class,
            DevModuleWipeMediaCommand::class,
            DevModuleSeedCommand::class,
            DevReinstallCommand::class,
            DevSeedCommand::class,
            DevWipeMediaCommand::class,
            MakeModuleCommand::class,
            MakeModuleMenuCommand::class,
            MakeModuleModelCommand::class,
            MakeModuleRoutesCommand::class,
            MakeModuleProviderCommand::class,
            MakeModuleLivewireComponentCommand::class,
            MakeModuleComposerCommand::class,
        ]);
    }
}
