<?php
namespace Skfw\Cabbage;

use Exception;
use Skfw\Interfaces\Cabbage\IHttpBodyContent;
use Skfw\Virtualize\VirtStdContent;
use Skfw\Virtualize\VirtStdIn;

class HttpBodyContent extends VirtStdIn implements IHttpBodyContent
{
    private array $_body;  // body as array
    private bool $_json_unpack;

    public function __construct(?VirtStdContent $content = null, ?array $post = null, ?int $max_size = null, bool $json_unpack = false)
    {
        parent::__construct($content, max_size: $max_size);

        $this->_body = $post ?? $_POST;
        $this->_json_unpack = $json_unpack;
    }

    public function body(): array
    {

        // json
        if ($this->_json_unpack)
        {
            $data = $this->json();  // collect json
            $this->_body = array_merge_recursive($this->_body, $data);
            $this->_json_unpack = false;  // once, no more
        }

        return $this->_body;
    }

    public function json(): array
    {
        if ($this->_json_unpack)
        {
            try {
                $this->seek(0);
                $text = $this->buffer() ?? '{}';  // get content
                return json_decode($text, associative: true) ?? [];
            } catch (Exception) {}
        }
        return [];
    }
}
