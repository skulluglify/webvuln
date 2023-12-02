<?php
namespace Skfw\Interfaces\Cabbage\Controllers;

use Generator;
use ReflectionClass;

interface ICabbageInspectApp
{
    public function workdir(): string;
    public static function get_namespace_from_script(string $script): ?string;
    public function get_reflect_class(string $page): ?string;
}

interface ICabbageInspectAppController extends ICabbageInspectApp
{
    public function get_middlewares_from_class(string $page): array;
    public function get_routers_from_class(string $page): Generator;
}