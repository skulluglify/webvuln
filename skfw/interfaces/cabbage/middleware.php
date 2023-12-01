<?php
namespace Skfw\Interfaces\Cabbage;

use Closure;

interface IMiddleware
{
    public function bind(Closure $next): void;
    public function next(IHttpRequest $request): ?IHttpResponse;
    public function handler(IHttpRequest $request): ?IHttpResponse;
}