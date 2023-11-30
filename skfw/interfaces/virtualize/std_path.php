<?php
namespace Skfw\Interfaces\Virtualize;

use Stringable;

interface IVirtStdPath extends Stringable
{
    public function basedir(): string;
    public function workdir(): string;
}