<?php
namespace App\Controllers;

use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\Route;

class UserController
{

    #[Route('/')]
    public function home(IHttpRequest $request): ?IHttpResponse
    {

        // coding ...

        return new HttpResponse('Hello, World!');
    }
}