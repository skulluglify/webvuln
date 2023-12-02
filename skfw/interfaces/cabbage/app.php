<?php
namespace Skfw\Interfaces\Cabbage;

interface IApp
{
    public function controllers(array $pages): void;
    public function middlewares(array $middlewares): void;
    public function run(): void;
}