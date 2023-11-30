<?php
namespace Skfw\Interfaces\Virtualize;

use Stringable;

interface IVirtStdContent extends Stringable
{
    public function name(): string;

    public function open_hook(?string $filename = null, bool $update = false): bool;

    public function read(int $length, int $offset = 0): ?string;

    public function buffer(): ?string;

    public function write(string $data): bool;

    public function readable(): bool;

    public function writable(): bool;

    public function size(): int;

    public function seek(int $offset): bool;

    public function closed(): bool;

    public function close(): bool;
}
