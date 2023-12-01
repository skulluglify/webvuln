<?php

namespace Skfw\Tags;

use Attribute;
use Skfw\interfaces\ITag;
use Stringable;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
readonly class Tag implements Stringable, ITag
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