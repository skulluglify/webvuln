<?php
namespace Skfw\Interfaces\Cabbage;

use Skfw\Interfaces\IFile;

interface IHttpFile extends IFile
{
    public function safe_name(): string;
    public function mimetype(): string;
}

interface IHttpFileCollector
{
    public function files(): array;
    public function file(string $name, int $case = 1): ?IHttpFile;
}