<?php
namespace Skfw\Interfaces;

interface IVirtStdFile
{
    public function file_name(): string;
    public function file_type(): string;
    public function file_path(): string;
    public function file_size(): int;
}