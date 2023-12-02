<?php
namespace Skfw\Cabbage\Controllers;

use Closure;
use ReflectionFunction;
use Skfw\Interfaces\Cabbage\Controllers\IDirectRouterController;
use Skfw\Interfaces\IVirtStdPathResolver;

class DirectRouterController implements IDirectRouterController
{
    private IVirtStdPathResolver $_path;
    private Closure $_method;

    public function __construct(IVirtStdPathResolver $path, Closure $method)
    {
        $this->_method = $method;
        $this->_path = $path;
    }
    public function path(): IVirtStdPathResolver
    {
        return $this->_path;
    }
    public function method(): Closure
    {
        return $this->_method;
    }
}
