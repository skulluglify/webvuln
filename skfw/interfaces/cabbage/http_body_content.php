<?php
namespace Skfw\Interfaces\Cabbage;

interface IHttpBodyContent
{
    public function body(): array;
    public function json(): array;
}