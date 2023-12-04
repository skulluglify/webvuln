<?php
namespace App\Controllers;

use Skfw\Cabbage\HttpResponse;
use Skfw\Enums\HttpMethod;
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

    #[Route('/todos')]
    public function todos(IHttpRequest $request): ?IHttpResponse
    {
        switch ($request->method()) {
            case HttpMethod::GET:

                $data = [
                    [
                        "name" => "Ahmad Asy Syafiq",
                        "age" => 21,
                    ],
                ];

                $temp = json_encode($data);
                return new HttpResponse($temp);


            case HttpMethod::POST:
                $body = $request->body();
                $name = array_key_exists("name", $body) ? $body["name"] : null;

                $timestamp = time_unix();

                if ($name !== null) return new HttpResponse("Your name: $name, created at $timestamp");
        }

        // method sah
        return new HttpResponse('Get out from here!');
    }
}