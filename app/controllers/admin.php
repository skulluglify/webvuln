<?php
namespace App\Controllers;

use App\Middlewares\ExampleMiddleware;
use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;

class AdminController {

    public function prefix(): string
    {
        return '/admin';
    }

    /**
     * @return IMiddleware[]
     */
    public function middlewares(): array
    {
        return [
            new ExampleMiddleware(),
        ];
    }

    #[PathTag(name: "Home Based", value: "/"), PathTag(name: "About", value: "/about/policy")]
    public function home(HttpRequest $request): ?HttpResponse {

        return new HttpResponse('Hello, Syafiq!');
    }
}
