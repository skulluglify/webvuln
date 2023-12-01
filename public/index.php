<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\Controllers\CabbageInspectAppController;
use Skfw\Interfaces\Cabbage\Controllers\IDirectRouterController;

$cwd = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app';

try {
    $page = 'admin';

    $request = new HttpRequest();
    $request_path = $request->path();

    $inspect = new CabbageInspectAppController($cwd);
    $routers = $inspect->get_direct_routers($page);
    foreach ($routers as $route)
    {
        if ($route instanceof IDirectRouterController)
        {
            $path = $route->path();
            $method = $route->method();

            echo str($request_path->equal($path, sandbox: true)) . '<br>';
            echo $path . '<br>';
            echo 'basename: '. $path->basename() . '<br>';
            echo 'dirname: '. $path->dirname() . '<br>';
        }
    }

} catch (Exception)
{
    print 'failed get direct routers';
}