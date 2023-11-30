<?php
namespace Skfw\Interfaces;

use PathSys;
use Stringable;

interface IVirtStdPathResolver extends Stringable
{
    static public function detect(string $path, bool $base = true): PathSys;
    static public function pack(
        array $paths,
        string $drive = "C",
        string $schema = "file",
        bool $base = true,
        bool $win_path_v2 = false,
        PathSys $sys = PathSys::POSIX): string;
    public function is_base_path(): bool;
    public function paths(): array;
    public function system(): PathSys;
    public function drive(): ?string;
    public function schema(): ?string;
    public function domain(): ?string;
    public function size(): int;
    public function join(string ...$paths): self;
    public function path(): string;
}
