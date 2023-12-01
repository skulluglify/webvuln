<?php

use Skfw\Cabbage\HttpHeader;
use Skfw\Cabbage\HttpInfoRequest;
use Skfw\Enums\HttpStatusCode;

include '../skfw/autoload.php';
//var_dump($_SERVER['QUERY_STRING']);
//var_dump($_SERVER['REQUEST_METHOD']);
//var_dump($_SERVER['REQUEST_URI']);
//var_dump($_SERVER['REQUEST_TIME']);


//$contentType = $_FILES['picture']['type'];
//header('content-type: $contentType');
//$data = file_get_contents($_FILES['picture']['tmp_name']);
//echo $data;

try {
    $collector = new \Skfw\Cabbage\HttpHeaderCollector();
    foreach ($collector->headers() as $header)
    {
        foreach ($header->values() as $value)
        {
            echo $header->name() . ': ' . $value . PHP_EOL;
        }
    }

//    $collector = new \Skfw\Cabbage\HttpFileCollector();
//    var_dump($collector->files());

//    $collector = new \Skfw\Cabbage\HttpParamCollector();
//    var_dump($collector->params());

    $content_type = str($collector->header('content-type'));
    $json_unpack = $content_type === 'application/json';

    $body_content = new \Skfw\Cabbage\HttpBodyContent(json_unpack: $json_unpack);
    var_dump($body_content->body());

    echo PHP_EOL;

    $path_resolver = new \Skfw\Virtualize\VirtStdPathResolver('C:\\Users\Guest\"My Document"\Games');
    echo str($path_resolver) . PHP_EOL;
    echo $path_resolver->system()->value . PHP_EOL;
    echo $path_resolver->drive() . PHP_EOL;

    echo PHP_EOL;

    $path_resolver = new \Skfw\Virtualize\VirtStdPathResolver('file:///foo/bar+bar2/book.log?param=go');
    echo str($path_resolver) . PHP_EOL;
    echo $path_resolver->system()->value . PHP_EOL;
    echo $path_resolver->schema() . PHP_EOL;

    echo PHP_EOL;

    $path_resolver = new \Skfw\Virtualize\VirtStdPathResolver('http://www.skfw.net:80/login?param=go');
    echo str($path_resolver) . PHP_EOL;
    echo $path_resolver->system()->value . PHP_EOL;
    echo $path_resolver->schema() . PHP_EOL;
    echo $path_resolver->domain() . PHP_EOL;

    echo PHP_EOL;

    $path_resolver = new \Skfw\Virtualize\VirtStdPathResolver('file://C:/Users/Guest/My+Documents/Public/index.html?param=go');
    echo str($path_resolver) . PHP_EOL;
    echo $path_resolver->system()->value . PHP_EOL;
    echo $path_resolver->schema() . PHP_EOL;
    echo $path_resolver->drive() . PHP_EOL;

    echo PHP_EOL;

    $path_resolver = new \Skfw\Virtualize\VirtStdPathResolver(getcwd() . DIRECTORY_SEPARATOR . 'example');
    echo str($path_resolver) . PHP_EOL;
    echo $path_resolver->system()->value . PHP_EOL;
    echo 'RELATIVE: ' . $path_resolver->relative()->posix() . PHP_EOL;
    echo 'ABSOLUTE: ' . $path_resolver->absolute() . PHP_EOL;

    echo PHP_EOL;

    $paths = ['.', '..', '..', 'foo', 'bar', '..', '..', 'tmp', 'var', 'run', '.', '..', 'book logs', 'book.log'];
    echo \Skfw\Virtualize\VirtStdPathResolver::pack($paths, base: true, win_path_v2: true, sys: PathSys::WINDOWS);
    // ../../tmp/var/book.log | base: false
    // /tmp/var/book.log | base: true

    // fake root
    // layer1 + layer2
    // pack(paths)[base => true] + pack(paths)[base => true]
    echo PHP_EOL;

    $resolver = new \Skfw\Virtualize\VirtStdPathResolver('main.zip');
    $resolver->join('book.log');
    echo $resolver->system()->name . PHP_EOL;
    var_dump($resolver->values());

    // on system
    // pack(paths)[base => true] + pack(paths)[base => false]
    echo PHP_EOL;

} catch (\Exception $e) {
    echo $e->getMessage();

}

//$request = new \Skfw\Cabbage\HttpRequest();
//echo $request->path();

//$response = new \Skfw\Cabbage\HttpResponse('Hello, World!', HttpStatusCode::OK);
//$response->sender();
