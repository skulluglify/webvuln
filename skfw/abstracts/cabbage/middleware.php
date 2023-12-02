<?php
namespace Skfw\Abstracts\cabbage;

use Closure;
use ReflectionException;
use ReflectionFunction;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;

abstract class MiddlewareAbs implements IMiddleware
{
    private Closure $_next_handler;
    public function __construct()
    {
        // default closure!
        $this->_next_handler = fn(mixed $req) => null;
    }
    public function bind(Closure $next): void
    {
        // binding new next handler!
        $this->_next_handler = $next;
    }

    public function next(IHttpRequest $request): ?IHttpResponse
    {
        try {
            $reflect = new ReflectionFunction($this->_next_handler);
            return $reflect->invoke($request);  // invoked!
        } catch (ReflectionException)  // main problem on internal server! (your code)
        { return null; }  // make it passing! (i don't care)
    }
    abstract public function handler(IHttpRequest $request): ?IHttpResponse;
}
