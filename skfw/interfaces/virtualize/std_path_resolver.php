<?php
namespace Skfw\Interfaces;

use PathSys;

interface IVirtStdPathResolver
{
    public function __toString(): string;
    static public function detect(string $path, bool $base = true): PathSys;
    static public function pack(
        array $paths,
        string $drive = "C",
        string $schema = "file",
        bool $base = true,
        PathSys $sys = PathSys::POSIX): string;
    public function is_base_path(): bool;
    public function paths(): array;
    public function system(): string;
    public function drive(): string;
    public function schema(): string;
    public function size(): int;
}
