<?php
namespace Skfw\Cabbage;

use HttpHeader;
use HttpParam;
use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;

class HttpRequest
{
    private HttpMethod $_method;
    private HttpStatusCode $_status_code;
    private HttpStatusMessage $_status_message;
    private array $_headers;
    private array $_params;
    private array $_files;
    private ?string $_content;

    public function method(): HttpMethod
    {

        return HttpMethod::GET;
    }

    public function status(): int
    {

        return 200;
    }

    /**
     * @return HttpHeader[]
     */
    public function headers(): array
    {

        return [];
    }

    /**
     * @return HttpParam[]
     */
    public function params(): array
    {

        return [];
    }

    public function content(): ?string
    {

        return null;
    }
}
