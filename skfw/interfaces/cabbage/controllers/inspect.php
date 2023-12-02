<?php
namespace Skfw\Interfaces\Cabbage\Controllers;

use Generator;
use ReflectionClass;
use Skfw\Interfaces\IVirtStdPathResolver;

interface ICabbageInspectApp
{
    public function workdir(): string;
    public static function get_namespace_from_script(string $script): ?string;
    public function get_reflect_class(string $page): ?string;
}

interface ICabbageResourceController
{
    public function middlewares(): array;
    public function prefix(): IVirtStdPathResolver;
}

interface ICabbageInspectAppController extends ICabbageInspectApp
{
    public function get_resource_from_class(string $page): ICabbageResourceController;
    public function get_routers_from_class(string $page): Generator;
}