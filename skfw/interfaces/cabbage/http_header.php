<?php
namespace Skfw\Interfaces\Cabbage;

interface IHttpHeader extends IValues
{
}

interface IHttpHeaderCollector
{
    public function headers(): array;
    public function header(string $name, int $case = 1): ?IHttpHeader;
}