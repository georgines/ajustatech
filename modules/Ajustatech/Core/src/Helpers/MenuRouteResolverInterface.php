<?php

namespace Ajustatech\Core\Helpers;

use stdClass;

interface MenuRouteResolverInterface
{
    public function resolveRoutes(stdClass $menu): stdClass;
}
