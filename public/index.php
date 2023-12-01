<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\Controllers\CabbageInspectAppController;
use Skfw\Interfaces\Cabbage\Controllers\IDirectRouterController;

$cwd = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app';

try {
    $page = 'admin';

    $request = new HttpRequest();
    $request->path();

    $inspect = new CabbageInspectAppController($cwd);
    $routers = $inspect->get_direct_routers($page);
    foreach ($routers as $route)
    {
        if ($route instanceof IDirectRouterController)
        {
            $path = $route->path();
            $method = $route->method();

            echo $path . PHP_EOL;
        }
    }

} catch (Exception)
{
    print 'failed get direct routers';
}