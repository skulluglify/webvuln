<?php

namespace Skfw\Interfaces\Cabbage;

interface IValues
{
    public function __toString(): string;
    public function getName(): string;

    public function getValues(): array;
    public function first(): ?string;
}
