<?php
namespace Skfw\Interfaces\Cabbage;

use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\IVirtStdPathResolver;

interface IHttpInfoRequest
{
    public function status(): ?HttpStatusCode;
    public function server_name(): string;
    public function server_port(): int;
    public function server_addr(): string;
    public function client_addr(): string;
    public function client_port(): int;
    public function server(): string;
    public function gateway(): string;
    public function scheme(): string;
    public function protocol(): string;
    public function root(): string;
    public function uri(): string;
    public function size(): int;
    public function type(): string;
    public function method(): HttpMethod;
    public function fast_cgi_role(): string;
    public function timestamp(): int;
    public function path(): IVirtStdPathResolver;
}