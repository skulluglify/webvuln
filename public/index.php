<?php
include '../skfw/autoload.php';

try {

    $request = new Skfw\Cabbage\HttpRequest();

    $path = '../app/data/public';
    $middleware = new \Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware($path);
    $response = $middleware->handler($request);
    if (!empty($response)) $response->sender();

} catch (Exception) {}
