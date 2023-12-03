<?php
namespace Skfw\Cabbage;

use Closure;
use Exception;
use ReflectionException;
use Skfw\Cabbage\Controllers\CabbageInspectAppController;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\Controllers\IDirectRouterController;
use Skfw\Interfaces\Cabbage\IApp;
use Skfw\Interfaces\Cabbage\IMiddleware;


class App implements IApp {

    private CabbageInspectAppController $_cabbage_inspect_app_controller;
    private array $_middlewares;
    private array $_pages;

    /**
     * @throws Exception
     */
    public function __construct(?string $cwd = null, string $workdir = 'controllers')
    {
        $this->_cabbage_inspect_app_controller = new CabbageInspectAppController($cwd, $workdir);
        $loader = $cwd . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_file($loader)) require_once $loader;
        $this->_middlewares = [];
        $this->_pages = [];
    }
    /**
     * @param IMiddleware[] $middlewares
     * @param HttpRequest $request
     * @param Closure|null $method
     * @return HttpResponse|null
     */
    private static function _middleware_handler(array $middlewares, HttpRequest $request, ?Closure $method = null): ?HttpResponse
    {
        $init = null;
        $bind = null;

        // chaining all middlewares!
        foreach ($middlewares as $middleware)
        {
            if ($middleware instanceof IMiddleware)
            {
                // start iteration!
                $handler = fn(HttpRequest $req): ?HttpResponse => $middleware->handler($req);
                if ($bind !== null) $bind($handler);
                else $init = $handler;

                // next iteration!
                $bind = fn(Closure $next) => $middleware->bind($next);
            }
        }

        // binding, and handling request!
        if ($bind !== null && $method !== null) $bind($method);
        return $init !== null ? $init($request) : ($method !== null ? $method($request) : null);
    }
    /**
     * @throws Exception
     */
    public function controllers(array $pages): void
    {
        foreach ($pages as $page)
        {
            if (!empty($page) && is_string($page))
            {
                $workdir = $this->_cabbage_inspect_app_controller->workdir();
                if (!safe_file_name($page)) throw new Exception("name of $page is not valid");
                if (!is_file($workdir . DIRECTORY_SEPARATOR . $page . '.php')) throw new Exception("file $page is not found");
                $this->_pages[] = $page;
            }
        }
    }
    public function middlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware)
        {
            // check middleware!
            if (!empty($middleware) && $middleware instanceof IMiddleware)
                $this->_middlewares[] = $middleware;
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function run(): void
    {
        $pages = $this->_pages;
        $middlewares = $this->_middlewares;

        $request = new HttpRequest();

        // handling middlewares!
        $response = self::_middleware_handler($middlewares, $request);

        if (empty($response))
        {
            foreach ($pages as $page)
            {
                $resource = $this->_cabbage_inspect_app_controller->get_resource_from_class($page);
                $routers = $this->_cabbage_inspect_app_controller->get_routers_from_class($page);

                // before check equal path!
                // $response = self::_middleware_handler($resource->middlewares(), $request);
                // if (empty($response)) { ...

                foreach ($routers as $route)
                {
                    if ($route instanceof IDirectRouterController)
                    {
                        $prefix = $resource->prefix()->clone();  // can take cloning!
                        $path = $prefix->join($route->path());  // join impact path by ref!
                        //echo $resource->prefix() . '<br>';
                        //echo $request->path() . '<br>';
                        //echo str($request->path()->equal($path, sandbox: true)) . '<br>';
                        if ($request->path()->equal($path, sandbox: true))
                        {
                            $method = $route->method();

                            // after check equal path!
                            $response = self::_middleware_handler($resource->middlewares(), $request, $method);
                            break;
                        }
                    }
                }

                //exit(1);

                // ... }
            }
        }

        // catch error!
        if (empty($response)) $response = new HttpResponse('/(0-0)/ Nothing on here!', HttpStatusCode::NOT_FOUND);

        // sending response to client!
        $response->sender();
    }
}
