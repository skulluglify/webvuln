<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\App;
use Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware;

$cwd = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'app']);
$assets = $cwd . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['data', 'public']);

$app = new App($cwd);
$app->controllers(['admin']);
$app->middlewares([new DataAssetsResourcesMiddleware($assets)]);
$app->run();
