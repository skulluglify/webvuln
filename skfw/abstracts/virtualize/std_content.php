<?php

namespace Skfw\Abstracts\Virtualize;

use Skfw\Interfaces\Virtualize\IVirtStdContent;

abstract class VirtStdContentAbs implements IVirtStdContent
{
    public int $chunk;
    public int $max_size;

    abstract public function __toString(): string;
    abstract public function name(): string;

    abstract public function open_hook(?string $filename = null, bool $update = false): bool;

    abstract public function read(int $length, int $offset = 0): ?string;

    abstract public function buffer(): ?string;

    abstract public function write(string $data): bool;

    abstract public function readable(): bool;

    abstract public function writable(): bool;

    abstract public function size(): int;

    abstract public function seek(int $offset): bool;

    abstract public function closed(): bool;

    abstract public function close(): bool;
}
