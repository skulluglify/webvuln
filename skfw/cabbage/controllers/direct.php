<?php
namespace Skfw\Cabbage\Controllers;

use ReflectionMethod;
use Skfw\Interfaces\Cabbage\Controllers\IDirectRouterController;
use Skfw\Interfaces\IVirtStdPathResolver;

class DirectRouterController implements IDirectRouterController
{
    private IVirtStdPathResolver $_path;
    private ReflectionMethod $_method;

    public function __construct(IVirtStdPathResolver $path, ReflectionMethod $method)
    {
        $this->_method = $method;
        $this->_path = $path;
    }
    public function path(): IVirtStdPathResolver
    {
        return $this->_path;
    }
    public function method(): ReflectionMethod
    {
        return $this->_method;
    }
}
