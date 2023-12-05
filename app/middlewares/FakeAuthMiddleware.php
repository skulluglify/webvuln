<?php
namespace App\Middlewares;

use Override;
use Skfw\Abstracts\MiddlewareAbs;
use Skfw\Cabbage\HttpResponse;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;

class FakeAuthMiddleware extends MiddlewareAbs
{
    #[Override]
    public function handler(IHttpRequest $request): ?IHttpResponse
    {
        $auth = $request->header('authorization')?->shift();  // get message by param!
        if (!empty($auth) && str_starts_with($auth, 'Bearer ')) return $this->next($request);
        return new HttpResponse('Huh! tidak ada kunci?', HttpStatusCode::UNAUTHORIZED);  // continue next request!
    }
}