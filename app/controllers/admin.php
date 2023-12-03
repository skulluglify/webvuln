<?php
namespace App\Controllers;

use App\Middlewares\ExampleMiddleware;
use Exception;
use Skfw\Cabbage\HttpResponse;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;
use Skfw\Tags\Route;

class AdminController {
    public string $assets;

    public function __construct() {
        $this->assets = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['..', 'data', 'public']);
    }


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
        ];
    }

    #[Route('/')]
    public function home(IHttpRequest $request): ?IHttpResponse
    {
        // $body = $request->body();
        $name = $request->param('name')?->shift();

        if (!empty($name)) return new HttpResponse("Hello, $name!");
        return new HttpResponse("Hello, World!");
    }
}
