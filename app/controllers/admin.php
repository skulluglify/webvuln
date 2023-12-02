<?php
namespace App\Controllers;

use App\Middlewares\ExampleMiddleware;
use Exception;
use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\HttpResponse;
use Skfw\Cabbage\Middlewares\DataAssetsResourcesMiddleware;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;

class AdminController {

    public function prefix(): string
    {
        return '/admin';
    }

    /**
     * @return IMiddleware[]
     * @throws Exception
     */
    public function middlewares(): array
    {
        return [
            new ExampleMiddleware(),
            new DataAssetsResourcesMiddleware(__DIR__ . '/../data/public', prefix: 'admin'),
        ];
    }

    #[PathTag(name: 'Give Read Permission On Icon File', value: '/icon.png')]
    #[PathTag(name: 'Give Read Permission On Page HTML', value: '/page.html')]
    public function resources(IHttpRequest $request): ?IHttpResponse { return null; }

    #[PathTag(name: "Home Page Index", value: "/"), PathTag(name: "About Policy", value: "/about/policy")]
    public function home(IHttpRequest $request): ?IHttpResponse
    {

        return new HttpResponse('Hello, Syafiq!');
    }
}
