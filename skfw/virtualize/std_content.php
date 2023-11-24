<?php

namespace Skfw\Virtualize;

use Override;
use Skfw\Abstracts\Virtualize\VirtStdContentAbs;
use Skfw\Interfaces\Virtualize\IVirtStdContent;
use Stringable;

class VirtStdContent extends VirtStdContentAbs implements IVirtStdContent, Stringable
{

    // private variables
    private int $_offset = 0;
    // private int $_whence = SEEK_SET;
    private bool $_readable;
    private bool $_writable;
    private string $_name;  // stdin
    private string $_content;
    private int $_size;
    private bool $_closed;

    // public configuration
    public int $chunk = 512;
    public int $max_size = 16777216;  // 16MB

    public function __construct(
        string $name,
        string $content = "",
        ?int $length = null,
        ?int $chunk = null,
        ?int $max_size = null,
        bool $readable = true,
        bool $writable = false,
    )
    {

        // hook variables
        $this->_name = $name;
        $this->_content = $content;

        $this->chunk = $chunk ?? $this->chunk;
        $this->max_size = $max_size ?? $this->max_size;

        $length = min($length ?? strlen($content), $this->max_size);
        $this->_content = substr($this->_content, 0, $length);
        $this->_size = $length;

        // virtualize file permission
        $this->_readable = $readable;
        $this->_writable = $writable;

        // initialize
        $this->_closed = false;
    }

    public function __destruct()
    {

        // auto closing
        $this->close();
    }

    #[Override]
    public function __toString(): string
    {

        // short return content
        return !$this->_closed ? $this->_content ?? "" : "";
    }

    private function _get_content(): ?string
    {
        if ($this->_readable) {

            $data = $this->_content;
            $data = substr($data, $this->_offset, $this->_size);
            $this->_offset = $this->_size;  // update virt offset
            return $data;
        }

        return null;
    }

    public function getName(): string
    {
        // copy-host no-references
        return $this->_name;
    }

    #[Override]
    public function openHook(?string $filename = null, bool $update = false): bool
    {
        // hooked file by open read and write todo some update purposes.

        // setup
        // $offset = 0;
        $filename = $filename ?? $this->_name;

        $mode = null;

        if ($this->_readable && !$update) {
            $mode = "r";

        } else
            if ($this->_writable && $update) {
                $mode = "w";

            } else
                if ($this->_readable && $this->_writable && $update) {
                    $mode = "w+";
                }

        if ($mode !== null) {

            if (!$update) {

                // reset content and length
                $this->_content = "";
                $this->_size = 0;
                $this->_offset = 0;
                // $this->_whence = SEEK_SET;
            }

            // open file as stream resource
            $stream = fopen($filename, $mode);
            // fseek($stream, $offset, $this->_whence);  // no required, virt can handle it!

            // read data
            if ($mode == "w" or $mode == "w+") {

                fwrite($stream, $this->_content);
            }

            if ($mode == "r" or $mode == "w+") {

                // initialize
                $chunk = $this->chunk;
                $quota_size = $this->max_size;
                $terminated = false;
                $content = "";
                $size = 0;

                while (!feof($stream)) {

                    // buffer all resource into content
                    $data = fread($stream, $chunk);
                    $length = strlen($data);

                    if ($quota_size < $length) {

                        // shrink and send signal terminated
                        $content .= substr($data, 0, $quota_size);
                        $terminated = true;
                    } else {

                        $content .= $data;
                    }

                    // update size
                    $size += $length;

                    // update quota size and flushing
                    $quota_size = $quota_size - $chunk;
                    fflush($stream);

                    if ($terminated) {
                        break;
                    }
                }

                $this->_content = $content;  // new assign data content
                $this->_size = $size;
            }

            // close stream resource
            fclose($stream);

            return true;
        }

        return false;
    }

    #[Override]
    public function read(int $length, int $offset = 0): ?string
    {

        // prevent all activities
        if ($this->readable()) {

            $offset += $this->_offset;

            // safe content
            $content = $this->_get_content();

            $this->_offset = $offset + $length;

            // safe length
            $length = min($length, $this->_size, $this->max_size);

            // virtualize get as stream resource
            return substr($content, $offset, $length);
        }

        return null;
    }

    #[Override]
    public function readAll(): ?string
    {

        return $this->readable() ? $this->_get_content() : null;
    }

    #[Override]
    public function write(string $data): bool
    {

        // prevent all activities
        if ($this->writable()) {

            // unsafe get content
            $content = $this->_content;

            $offset = $this->_offset;
            $quota_size = $this->max_size - $offset;

            // defined length of data
            $length = strlen($data);

            // safe length
            $length = min($length, $quota_size);

            // safe content
            $data = substr($data, 0, $length);
            $content = substr($content, 0, $offset) . $data;

            // virtualize get as stream resource
            $this->_content = $content;
            $this->_size = $offset + $length;  // some offset deflate

            // update virt offset
            $this->_offset = $this->_size;

            return true;
        }

        return false;
    }

    #[Override]
    public function readable(): bool
    {

        return !$this->_closed ? $this->_readable : false;
    }

    #[Override]
    public function writable(): bool
    {

        return !$this->_closed ? $this->_writable : false;
    }

    #[Override]
    public function size(): int
    {
        return !$this->_closed ? $this->_size : 0;
    }

    #[Override]
    public function seek(int $offset): bool
    {

        $this->_offset = $offset;
        // $this->_whence = $whence;
        return false;
    }

    #[Override]
    public function closed(): bool
    {

        return $this->_closed;
    }

    #[Override]
    public function close(): bool
    {

        $this->_closed = true;
        $this->_content = "";
        $this->_size = 0;
        return true;
    }
}