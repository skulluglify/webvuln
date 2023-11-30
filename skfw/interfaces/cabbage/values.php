<?php
namespace Skfw\Interfaces\Cabbage;

use Stringable;

interface IValues extends Stringable
{
    public function name(): string;

    public function values(): array;
    public function shift(): ?string;
}
