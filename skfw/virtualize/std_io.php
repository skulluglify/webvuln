<?php

namespace Skfw\Virtualize;

use Override;
use Skfw\Interfaces\Virtualize\IVirtStdContent;
use Stringable;

class VirtStdIn implements Stringable, IVirtStdContent
{

    private VirtStdContent $content;

    public function __construct(?VirtStdContent $content = null, ?int $max_size = null)
    {
        $this->content = $content ?? new VirtStdContent(name: 'php://input', max_size: $max_size, readable: true, writable: false);
        $this->content->open_hook();
    }

    #[Override]
    public function __toString(): string
    {

        return $this->content->__toString();
    }

    #[Override]
    public function name(): string
    {

        return $this->content->name();
    }

    #[Override]
    public function open_hook(?string $filename = null, bool $update = false): bool
    {

        return $this->content->open_hook(filename: $filename, update: $update);
    }

    #[Override]
    public function read(int $length, int $offset = 0): ?string
    {

        return $this->content->read(length: $length, offset: $offset);
    }

    #[Override]
    public function buffer(): ?string
    {

        return $this->content->buffer();
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

class VirtStdOut implements Stringable, IVirtStdContent
{

    private VirtStdContent $content;

    public function __construct(?VirtStdContent $content = null)
    {
        $this->content = $content ?? new VirtStdContent(name: 'php://output', readable: false, writable: true);
        $this->content->open_hook();
    }

    #[Override]
    public function __toString(): string
    {

        return $this->content->__toString();
    }

    #[Override]
    public function name(): string
    {

        return $this->content->name();
    }

    #[Override]
    public function open_hook(?string $filename = null, bool $update = false): bool
    {

        return $this->content->open_hook(filename: $filename, update: $update);
    }

    #[Override]
    public function read(int $length, int $offset = 0): ?string
    {

        return $this->content->read(length: $length, offset: $offset);
    }

    #[Override]
    public function buffer(): ?string
    {

        return $this->content->buffer();
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
