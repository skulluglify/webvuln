<?php
namespace Skfw\Interfaces\Cabbage;

interface IMiddleware
{
    public function handler(IHttpRequest $request): ?IHttpResponse;
}