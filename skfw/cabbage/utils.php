<?php

use Skfw\Cabbage\HttpHeader;
use Skfw\Interfaces\Cabbage\IHttpHeader;

/**
 * @param array<string, string|array|IHttpHeader> $data
 * @return array<int, IHttpHeader>
 */
function headers(array $data): array
{
    $temp = [];
    foreach ($data as $key => $value)
    {
        if (!empty($key) && !empty($value) && is_string($key))
        {
            if (is_string($value)) $temp[] = new HttpHeader($key, str_getcsv($value));
            else if (is_array($value)) $temp = [...$temp, ...headers($value)];
            else if ($value instanceof IHttpHeader) $temp[] = $value;
            else continue;
        }
    }
    return $temp;
}