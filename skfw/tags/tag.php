<?php

namespace Skfw\Tags;

use Attribute;
use Skfw\interfaces\ITag;
use Stringable;

#[Attribute]
readonly class Tag implements ITag, Stringable
{
    public string $name;
    public ?string $value;

    function __construct(string $name, ?string $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    function __toString(): string
    {
        return $this->name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): ?string
    {
        return $this->value;
    }
}