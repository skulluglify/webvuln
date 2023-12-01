<?php
namespace Skfw\Interfaces\Cabbage\Controllers;

use ReflectionMethod;
use Skfw\Interfaces\IVirtStdPathResolver;

interface IDirectRouterController
{
    public function path(): IVirtStdPathResolver;
    public function method(): ReflectionMethod;
}