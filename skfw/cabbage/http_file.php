<?php
namespace Skfw\Cabbage;

use Skfw\Errors\Virtualize\VirtStdFileSizeDoNotMatch;
use Skfw\Errors\Virtualize\VirtStdFileTypeDoNotMatch;
use Skfw\Interfaces\IFile;
use Skfw\Virtualize\VirtStdFile;
use function Sodium\compare;

class HttpFile extends VirtStdFile implements IFile
{

    public function getSafeName(): string
    {

        return $this->getFileName();
    }

    public function mimetype(): string
    {

        return $this->getFileType();
    }
}


class HttpFileCollector
{

    private array $_http_files;

    /**
     * @throws VirtStdFileTypeDoNotMatch
     * @throws VirtStdFileSizeDoNotMatch
     */
    public function __construct(
        ?array $files = null,
        ?int $chunk = null,
        ?int $max_size = null,
        bool $readable = true,
        bool $writable = false,
    )
    {
        $this->_http_files = [];
        $files = $files ?? $_FILES;

        foreach ($files as $key => $file)
        {

            $this->_http_files[$key] = new HttpFile(
                file: $file,
                chunk: $chunk,
                max_size: $max_size,
                readable: $readable,
                writable: $writable,
            );
        }
    }

    /**
     * @return array<string, HttpFile>
     */
    public function files(): array
    {

        return $this->_http_files;
    }

    public function file(string $name, int $case = 1): ?HttpFile
    {
        $name = trim($name);
        foreach ($this->_http_files as $file)
        {
            $key = $file->getName();  // unsafe named comparison
            if (str_comp_case($name, $key, $case)) return $file;
        }

        return null;
    }
}
