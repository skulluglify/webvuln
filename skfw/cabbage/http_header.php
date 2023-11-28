<?php
namespace Skfw\Cabbage;

use Skfw\Cabbage\Values;
use Skfw\Interfaces\Cabbage\IValues;

readonly class HttpHeader extends Values implements IValues
{
}

class HttpHeaderCollector
{
    private array $_http_headers;

    public function __construct(?array $server = null)
    {
        $this->_http_headers = [];
        $server = $server ?? $_SERVER;

        foreach ($server as $key => $value)
        {
            // $key = upper($key);
            if (str_starts_with($key, 'HTTP_'))
            {

                $key = str_replace('_', '-', substr($key, 5));
                $key = capitalize_each_word($key);
                $values = str_getcsv($value);

                $this->_http_headers[] = new HttpHeader(
                    name: $key,
                    values: $values,
                );
            }
        }
    }

    /**
     * @return array<string, HttpHeader>
     */
    public function headers(): array
    {

        return $this->_http_headers;
    }

    public function header(string $name, int $case = 1): ?HttpHeader
    {
        $name = trim($name);
        foreach ($this->_http_headers as $header)
        {
            $key = $header->getName();  // unsafe named comparison
            if (str_comp_case($name, $key, $case)) return $header;
        }

        return null;
    }
}
