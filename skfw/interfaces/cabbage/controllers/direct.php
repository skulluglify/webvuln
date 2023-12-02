<?php
namespace Skfw\Interfaces\Cabbage\Controllers;

use Closure;
use Skfw\Interfaces\IVirtStdPathResolver;

interface IDirectRouterController
{
    public function path(): IVirtStdPathResolver;
    public function method(): Closure;
}