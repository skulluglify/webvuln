<?php
namespace Skfw\interfaces;

interface ITag {
    function __toString(): string;
    public function getName(): string;
    public function getValue(): ?string;
}
