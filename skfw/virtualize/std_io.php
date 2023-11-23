<?php

namespace Skfw\Virtualize;

use Override;
use Skfw\Interfaces\Virtualize\IVirtStdContent;
use Stringable;

class VirtStdIn implements IVirtStdContent, Stringable
{

    private VirtStdContent $content;

    public function __construct(?VirtStdContent $content = null)
    {
        $this->content = $content ?? new VirtStdContent(filename: "php://input", readable: true, writable: false);
        $this->content->openHook();
    }

    #[Override]
    public function __toString(): string
    {

        return $this->content->__toString();
    }

    #[Override]
    public function openHook(?string $filename = null, bool $update = false): bool
    {

        return $this->content->openHook(filename: $filename, update: $update);
    }

    #[Override]
    public function read(int $length, int $offset = 0): ?string
    {

        return $this->content->read(length: $length, offset: $offset);
    }

    #[Override]
    public function readAll(): ?string
    {

        return $this->content->readAll();
    }

    #[Override]
    public function write(string $data): bool
    {

        return $this->content->write(data: $data);
    }

    #[Override]
    public function readable(): bool
    {

        return $this->content->readable();
    }

    #[Override]
    public function writable(): bool
    {

        return $this->content->writable();
    }

    #[Override]
    public function size(): int
    {

        return $this->content->size();
    }

    #[Override]
    public function seek(int $offset): bool
    {

        return $this->content->seek(offset: $offset);
    }

    #[Override]
    public function closed(): bool
    {

        return $this->content->closed();
    }

    #[Override]
    public function close(): bool
    {

        return $this->content->close();
    }
}

class VirtStdOut implements IVirtStdContent, Stringable
{

    private VirtStdContent $content;

    public function __construct(?VirtStdContent $content = null)
    {
        $this->content = $content ?? new VirtStdContent(filename: "php://output", readable: false, writable: true);
        $this->content->openHook();
    }

    #[Override]
    public function __toString(): string
    {

        return $this->content->__toString();
    }

    #[Override]
    public function openHook(?string $filename = null, bool $update = false): bool
    {

        return $this->content->openHook(filename: $filename, update: $update);
    }

    #[Override]
    public function read(int $length, int $offset = 0): ?string
    {

        return $this->content->read(length: $length, offset: $offset);
    }

    #[Override]
    public function readAll(): ?string
    {

        return $this->content->readAll();
    }

    #[Override]
    public function write(string $data): bool
    {

        return $this->content->write(data: $data);
    }

    #[Override]
    public function readable(): bool
    {

        return $this->content->readable();
    }

    #[Override]
    public function writable(): bool
    {

        return $this->content->writable();
    }

    #[Override]
    public function size(): int
    {

        return $this->content->size();
    }

    #[Override]
    public function seek(int $offset): bool
    {

        return $this->content->seek(offset: $offset);
    }

    #[Override]
    public function closed(): bool
    {

        return $this->content->closed();
    }

    #[Override]
    public function close(): bool
    {

        return $this->content->close();
    }
}