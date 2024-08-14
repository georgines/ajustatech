<?php

namespace Ajustatech\Customer\Providers;

use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;

class CustomerServiceProvider extends ServiceProvider
{
   
    public function register()
    {  
        $this->loadRoutesFrom(base_path("modules/Ajustatech/Customer/src/Routes/web.php"));
        $this->loadViewsFrom(base_path("modules/Ajustatech/Customer/src/Views"), "customer");
        $this->loadMigrationsFrom(base_path("modules/Ajustatech/Customer/src/migrations"));
    }

    public function boot()
    {
        $verticalMenu = json_decode(file_get_contents( base_path("modules/Ajustatech/Customer/src/menu/verticalMenu.json")));
        $horizontalMenu = json_decode(file_get_contents( base_path("modules/Ajustatech/Customer/src/menu/verticalMenu.json")));

        $menu = app(MenuManagerInterface::class);
        $menu->addVerticalMenu($verticalMenu);
        $menu->addHorizontalMenu($horizontalMenu);
    }
}
