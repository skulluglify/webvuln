<?php
namespace Skfw\Cabbage;

use Skfw\Interfaces\Cabbage\IHttpHeader;
use Skfw\Interfaces\Cabbage\IHttpHeaderCollector;
use Skfw\Interfaces\Cabbage\IValues;

readonly class HttpHeader extends Values implements IValues, IHttpHeader
{
    public function __construct(string $name, array $values = [])
    {
        // normalize key name!
        $name = preg_replace('/(^((_|\s)+)|((_|\s)+)$)/i', '', $name);
        $name = preg_replace('/(_|\s)+/i', '-', $name);

        // capitalize each word on key, trimming all values!
        $name = capitalize_each_word($name);  // capitalize each word!
        parent::__construct($name, array_map(fn(string $v): string => trim($v), $values));
    }
}

class HttpHeaderCollector implements IHttpHeaderCollector
{
    // array<string, HttpHeader>
    private array $_http_headers;

    public function __construct(?array $server = null)
    {
        $this->_http_headers = [];
        $server = $server ?? $_SERVER;

        foreach ($server as $key => $value)
        {
            $value = str($value);  // value must be string!
            // key is absolutely upper case!
            if (str_starts_with($key, 'HTTP_'))
            {

                $key = str_replace('_', '-', substr($key, 5));
                $cew_key = capitalize_each_word($key);
                $values = str_getcsv($value);

                // key must be capitalized each word!
                $this->_http_headers[$cew_key] = new HttpHeader(
                    name: $cew_key,
                    values: $values,
                );
            }
        }

        // fix header values
        $this->_fix_header('CONTENT_TYPE');
        $this->_fix_header('CONTENT_LENGTH');
    }
    private function _fix_header(string $key): void
    {
        // make it use char '_'!
        $key = str_replace('-', '_', $key);
        if (isset($_SERVER[$key]))
        {
            $value = str($_SERVER[$key]);  // must be string!

            // make it use char '-'!
            $key = str_replace('_', '-', $key);
            $cew_key = capitalize_each_word($key);

            if (isset($this->_http_headers[$cew_key]))
            {
                // check value is valid or not!
                $content_type = $this->_http_headers[$cew_key];
                $shift = $content_type->shift();  // get first of values

                // first of value not be empty and equal than value!
                if (empty($shift) or $shift !== $value) $this->_http_headers[$cew_key] = new HttpHeader(
                    name: $cew_key,
                    values: [$value],
                );
            } else
            {
                // added new header, if empty set!
                $this->_http_headers[$cew_key] = new HttpHeader(
                    name: $cew_key,
                    values: [$value],
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

    public function header(string $name, int $case = 1): ?IHttpHeader
    {
        $name = trim($name);
        foreach ($this->_http_headers as $header)
        {
            if ($header instanceof IHttpHeader)
            {
                $key = $header->name();  // unsafe named comparison
                if (str_comp_case($name, $key, $case)) return $header;
            }
        }

        return null;
    }
}
