<?php
namespace Skfw\Interfaces\Cabbage\Controllers;

use Generator;
use ReflectionClass;

interface ICabbageInspectApp
{
    public static function get_namespace_from_script(string $script): ?string;
    public function get_reflect_class(string $page): ?ReflectionClass;
}

interface ICabbageInspectAppController extends ICabbageInspectApp
{
    public function get_direct_routers(string $page): Generator;
}