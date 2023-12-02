<?php
namespace App\Middlewares;

use Override;
use ReflectionException;
use Skfw\Abstracts\cabbage\MiddlewareAbs;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;

class ExampleMiddleware extends MiddlewareAbs
{
    #[Override]
    public function handler(IHttpRequest $request): ?IHttpResponse
    {
        $param = $request->param('message');
        if (!empty($param))
        {
            $message = $param->shift();
            if (!empty($message)) return new HttpResponse("You got message: $message");
        }
        return $this->next($request);
    }
}