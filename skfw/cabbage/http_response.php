<?php
namespace Skfw\Cabbage;

use HttpHeader;
use HttpParam;
use Skfw\Enums\HttpMethod;

class HttpResponse
{
    public int $status_code;
    public array $headers;
    public string $content;
    public ?int $length;
    public ?string $message;
}
