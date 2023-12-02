<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\App;
use Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware;

$cwd = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app';
$assets = $cwd . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'public';

$app = new App($cwd);
$app->controller(['admin']);
$app->middlewares([new DataAssetsResourcesMiddleware($assets)]);
$app->run();
