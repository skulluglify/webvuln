<?php
namespace Skfw\Interfaces;

interface IFile
{
    public function getName(): string;
    public function getSafeName(): string;
    public function mimetype(): string;
    public function size(): int;
}