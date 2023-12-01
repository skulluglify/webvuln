<?php

namespace Skfw\Tags;

use Attribute;
use Skfw\interfaces\ITag;
use Stringable;

#[Attribute]
readonly class PathTag extends Tag implements Stringable, ITag
{
}
