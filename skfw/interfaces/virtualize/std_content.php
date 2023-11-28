<?php

namespace Skfw\Interfaces\Virtualize;

interface IVirtStdContent
{
    public function __toString(): string;  // std_content propagation
    public function GetName(): string;

    public function openHook(?string $filename = null, bool $update = false): bool;

    public function read(int $length, int $offset = 0): ?string;

    public function readAll(): ?string;

    public function write(string $data): bool;

    public function readable(): bool;

    public function writable(): bool;

    public function size(): int;

    public function seek(int $offset): bool;

    public function closed(): bool;

    public function close(): bool;
}
