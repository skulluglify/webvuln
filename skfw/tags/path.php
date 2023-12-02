<?php

namespace Skfw\Tags;

use Attribute;
use Skfw\interfaces\ITag;
use Stringable;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
readonly class PathTag extends Tag implements Stringable, ITag
{
}
