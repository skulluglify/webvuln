<?php
namespace App\Middlewares;

use Override;
use Skfw\Abstracts\MiddlewareAbs;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;

class ExampleMiddleware extends MiddlewareAbs
{
    #[Override]
    public function handler(IHttpRequest $request): ?IHttpResponse
    {
        $message = $request->param('message')?->shift();  // get message by param!
        if (!empty($message)) return new HttpResponse("You got message: $message");  // stop request, push response!
        return $this->next($request);  // continue next request!
    }
}