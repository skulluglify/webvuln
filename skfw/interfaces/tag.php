<?php
namespace Skfw\interfaces;

use Stringable;

interface ITag extends Stringable
{
    public function name(): string;
    public function value(): ?string;
}
