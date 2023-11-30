<?php
namespace Skfw\Interfaces\Cabbage;

interface IHttpParam extends IValues
{
}

interface IHttpParamCollector
{
    public function params(): array;
    public function param(string $name, int $case = 1): ?IHttpParam;
}