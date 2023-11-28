<?php
namespace Skfw\Cabbage;

use Skfw\Cabbage\Values;
use Skfw\Interfaces\Cabbage\IValues;

readonly class HttpParam extends Values implements IValues
{
}

class HttpParamCollector
{
    private array $_http_params;

    public function __construct(?array $server = null, ?array $get = null)
    {
        $this->_http_params = [];
        $server = $server ?? $_SERVER;
        $get = $get ?? $_GET;  // backup, query

        $query = array_key_exists('QUERY_STRING', $server) ? $server['QUERY_STRING'] : null;
        $uri = array_key_exists('REQUEST_URI', $server) ? $server['REQUEST_URI'] : null;

        $uri = $query ?? $uri;
        if ($uri !== null)
        {
            $queries = query_decode($uri);
            foreach ($queries as $key => $values)
            {
                $this->_http_params[$key] = new HttpParam(
                    name: $key,
                    values: $values,
                );
            }
        } else
        {
            foreach ($get as $key => $value)
            {
                $this->_http_params[$key] = new HttpParam(
                    name: $key,
                    values: [$value],
                );
            }
        }
    }

    /**
     * @return array<string, HttpParam>
     */
    public function params(): array
    {

        return $this->_http_params;
    }

    public function param(string $name, int $case = 1): ?HttpParam
    {
        $name = trim($name);
        foreach ($this->_http_params as $param)
        {
            $key = $param->getName();  // unsafe named comparison
            if (str_comp_case($name, $key, $case)) return $param;
        }

        return null;
    }
}
