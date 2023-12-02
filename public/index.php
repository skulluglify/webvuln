<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\App;
use Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware;

$cwd = __DIR__ . '/../app';
$assets = $cwd . '/data/public';

$app = new App($cwd);
$app->controller(['admin']);
$app->middlewares([new DataAssetsResourcesMiddleware($assets)]);
$app->run();
