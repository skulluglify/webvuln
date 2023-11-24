<?php
namespace Skfw\Virtualize;

use Exception;
use finfo;
use Skfw\Errors\Virtualize\VirtStdFileError;
use Skfw\Errors\Virtualize\VirtStdFileSizeDoNotMatch;
use Skfw\Errors\Virtualize\VirtStdFileTypeDoNotMatch;
use Skfw\Interfaces\IVirtStdFile;

class VirtStdFile extends VirtStdContent implements IVirtStdFile
{
    private string $_file_name;  // safe name
    private string $_file_type;  // safe type
    private string $_file_path;  // direct path
    private int $_file_size;  // direct size

    /**
     * @throws VirtStdFileTypeDoNotMatch
     * @throws VirtStdFileSizeDoNotMatch
     * @throws Exception
     */
    public function __construct(
        array $file,
        ?int $chunk = null,
        ?int $max_size = null,
        bool $readable = true,
        bool $writable = false,
    )
    {
        // collect from file directory
        $name = $file["name"];
        $type = $file["type"];
        $size = $file["size"];
        $path = $file["path"] ?? $file["tmp_name"];  // support for '$_FILES' variable

        if (empty($name) or empty($type) or empty($size) or empty($path))
        {

            throw new VirtStdFileError("missing file info");
        }

        $data = file_get_contents($path);
        $content = $data;

        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $content_type = $file_info->buffer($content);

        if (strtolower($type) !== strtolower($content_type))
        {

            throw new VirtStdFileTypeDoNotMatch("file type don't match for 'Content-Type' header");
        }

        $content_size = strlen($content);

        if ($size !== $content_size)
        {

            throw new VirtStdFileSizeDoNotMatch("file size don't match for 'Content-Length' header");
        }

        parent::__construct(
            name: $name,
            content: $content,
            length: $size,
            chunk: $chunk,
            max_size: $max_size,
            readable: $readable,
            writable: $writable,
        );

        // safe file name
        $name = safe_file_name($name);

        $this->_file_name = $name;
        $this->_file_type = $type;
        $this->_file_path = $path;
        $this->_file_size = $size;
    }

    public function getFileName(): string
    {

        return $this->_file_name;
    }

    public function getFileType(): string
    {

        return $this->_file_type;
    }

    public function getFilePath(): string
    {

        return $this->_file_path;
    }

    public function getFileSize(): int
    {

        return $this->_file_size;
    }
}
