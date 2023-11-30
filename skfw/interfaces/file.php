<?php
namespace Skfw\Interfaces;

interface IFile
{
    public function name(): string;
    public function safe_name(): string;
    public function mimetype(): string;
    public function size(): int;
}