<?php
include '../skfw/autoload.php';

use Skfw\Cabbage\App;
use Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware;

$workdir = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'app']);
$assets = $workdir . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['data', 'public']);

$app = new App($workdir);
$app->controllers(['admin', 'user']);
$app->middlewares([new DataAssetsResourcesMiddleware($assets)]);
$app->run();
