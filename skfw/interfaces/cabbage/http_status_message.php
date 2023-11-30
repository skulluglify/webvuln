<?php
namespace Skfw\Interfaces\Cabbage;

use Stringable;

interface IHttpStatusMessage extends Stringable
{
    public function message(): string;
}
