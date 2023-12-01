<?php
namespace Skfw\Interfaces;

use PathSys;
use Stringable;

interface IVirtStdPathResolver extends Stringable
{
    public static function detect(string $path, bool $base = true): PathSys;
    public static function pack(
        array $values,
        string $drive = "C",
        string $schema = "file",
        bool $base = true,
        bool $win_path_v2 = false,
        PathSys $sys = PathSys::POSIX): string;
    public function is_base_dir(): bool;
    public function values(): array;
    public function system(): PathSys;
    public function drive(): ?string;
    public function schema(): ?string;
    public function domain(): ?string;
    public function size(): int;
    public function join(string ...$paths): self;
    public function path(): string;
    public function sandbox(?PathSys $sys = null): IVirtStdPathResolver;
    public function is_sandbox(): bool;
    public function is_network(): bool;
    public function is_posix(): bool;
    public function equal(string|IVirtStdPathResolver $path, bool $sandbox = false, bool $relative = false, int $case = 1): bool;
    public function absolute(int $case = 1): IVirtStdPathResolver;
    public function relative(int $case = 1): IVirtStdPathResolver;
    public function posix(?bool $base = null): string;
    public function network(?bool $base = null): string;
    public function windows(?bool $base = null): string;
    public function repack(?array $values = null, ?string $drive = null, ?string $schema = null, ?bool $base = null, ?PathSys $sys = null): string;
    public function dirname(): string;
    public function basename(): string;

}
