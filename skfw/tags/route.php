<?php

namespace Skfw\Tags;

use Attribute;
use Skfw\interfaces\ITag;
use Stringable;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
readonly class Route extends PathTag implements Stringable, ITag
{
    public function __construct(string $value)
    {
        parent::__construct('Route', $value);
    }
}
