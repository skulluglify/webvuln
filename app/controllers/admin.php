<?php
namespace App\Controllers;

use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;

class AdminController {

    public function __construct() {


    }

    /**
     * @return array<int, IMiddleware>
     */
    public function middlewares(): array
    {
        return [];
    }

    #[PathTag(name: "Home Based", value: "/"), PathTag(name: "About", value: "/about/policy")]
    public function home(HttpRequest $request): ?HttpResponse {

        return new HttpResponse('Hello, World!');
    }
}
