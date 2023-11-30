<?php
namespace Skfw\Interfaces\Cabbage;

use Skfw\Enums\HttpStatusCode;
use Stringable;

interface IHttpResponse extends Stringable
{
    public function sender(): void;
    public function sending(): bool;
    public function data(): string;
    public function status(): HttpStatusCode;
    public function headers(): array;
    public function protocol(): string;
}
