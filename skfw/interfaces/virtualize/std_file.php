<?php
namespace Skfw\Interfaces;

interface IVirtStdFile
{
    public function getFileName(): string;
    public function getFileType(): string;
    public function getFilePath(): string;
    public function getFileSize(): int;
}