<?php

function is_safe_name(string $name): bool
{
    // trimming name string
    $name = trim($name);

    // validation name string
    if ($name !== '')
    {
        $data = str_split($name);
        // chars allowed, empty space, digits, alphabets, valid symbols
        $stack = ' -.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        foreach ($data as $char)
        {
            if (!str_contains($stack, $char)) return false;
        }

        // all checks
        return true;
    }

    // empty string
    return false;
}

// TODO: array|string, array is can take like references
/**
 * @throws Exception
 */
function safe_file_name(string $name): string
{
    $name = trim($name);  // trimming empty spaces

    $array_name = str_split($name);

    $alpha_num = '-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
    $array_alpha_num = str_split($alpha_num);
    $alpha_num_length = strlen($alpha_num);

    // name is empty string
    if ($name === '')
    {

        $data = $array_alpha_num;
        shuffle($data);

        // shrinking
        $shrink = random_int(20, 50);
        $data = array_splice($data, 0, $shrink);
        shuffle($data);

        // returning
        return 'bk_' . join($data);  // start at 'bk_' for header
    }

    $temp = '';

    foreach ($array_name as $char)
    {
        // character contains alpha numbers or empty spaces
        if (str_contains($alpha_num, $char) or $char == ' ')
        {

            $temp .= $char;
        } else
        {

            // fixed indexes number
            $i = random_int(0, $alpha_num_length - 1);
            $temp .= $array_alpha_num[$i];
        }
    }

    return $temp;
}

// TODO: array|string, array is can take like references
function capitalize_each_word(string $text): string
{

    $data = str_split($text);
    $alpha_upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alpha_lower = 'abcdefghijklmnopqrstuvwxyz';
    $alpha = $alpha_upper . $alpha_lower;

    $temp = '';
    $f = 0;  // first word must be capital word

    foreach ($data as $char)
    {

        if ($f === 0)
        {
            $temp .= strtoupper($char);
            $f = 1;

            continue;
        }

        if (!str_contains($alpha, $char))
        {
            $temp .= $char;
            $f = 0;

            continue;
        }

        $temp .= strtolower($char);
    }

    return $temp;
}


function str_comp_case(string $left, string $right, int $case = 1): bool
{
    return match ($case)
    {
        0 => $left === $right,
        1 => strtolower($left) === strtolower($right),
        default => false,
    };
}

/**
 * @param string $query
 * @return string[][]
 */
function query_decode(string $query): array
{
    // maybe query is URI component
    $data = str_getcsv($query, '?');
    $query = array_pop($data) ?? '';

    $temp = [];
    $queries = str_getcsv($query, '&');
    foreach ($queries as $query)
    {
        $data = str_getcsv($query, '=');
        $length = count($data);

        if ($length > 0)
        {
            $key = $data[0];
            $value = null;

            if ($length > 1)
            {
                // acquire new value
                $value = $data[1];

                // decode query by url decode function
                $decoded = urldecode($value);

                // store data, acquire new data query, array append
                if (array_key_exists($key, $temp))
                {
                    $temp[$key][] = $decoded;
                    continue;
                }

                // store data, acquire new data query, create new array
                $temp[$key] = [$decoded];
                continue;
            }

            // maybe value is not set.
            // store data, acquire new data query, array append
            if (array_key_exists($key, $temp))
            {
                $temp[$key][] = null;  // nullable
                continue;
            }

            // store data, acquire new data query, create new array
            $temp[$key] = [null];  // nullable
        }
    }


    return $temp;
}

// casting into string, hook 'toString' function
function str(?Stringable $any): string
{
    // nullable, i hate php!@#$
    return $any !== null ? ''.$any : '';
}


// csv extractor
function map_mix(array $keys, array $values): array
{
    // length of values less than length of keys, set null
    // length of values greater than length of keys, shrinking
    $temp = [];
    $n = count($values);
    foreach ($keys as $i => $key)
    {
        if ($i < $n)
        {
            $temp[$key] = $values[$i];
            continue;
        }
        $temp[$key] = null;
    }

    // returning
    return $temp;
}

function unpack_csv_file(string $data, ?string $header = null, bool $skip_first_line = true): array
{
    // unpacking csv file into array
    // set header by string with comma separator value
    $skip_first_line = $skip_first_line && $header !== null;

    $lines = explode('\n', $data);
    $header = $header ?? array_shift($lines);  // remove head
    $keys = str_getcsv(strtolower($header));  // get keys by header, set to lower case

    $temp = [];
    foreach ($lines as $i => $value)
    {
        // skip first line must be first index and header set null
        if ($skip_first_line && $i == 0) continue;

        // unpacking data
        $values = str_getcsv($value);
        $dict = map_mix($keys, $values);
        $temp[] = $dict;
    }

    // returning
    return $temp;
}
// end of csv extractor
// server utils
function get_domain_by_uri(string $uri): ?string
{
    $domain = null;
    // $uri = "http://www.skfw.net/login?param=go";

    $matches = [];
    // ^
    // (([a-z]+):\/\/|)
    // (www[.]|)
    // ([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|)
    // (([.]([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|))+)
    // (\/|$)

    preg_match('/^(([a-z]+):\/\/|)(www[.]|)([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|)(([.]([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|))+)(\/|$)/i', $uri, $matches) . PHP_EOL;

    if (count($matches) > 0) {
        $url = $matches[0];

        $matches = [];
        preg_match('/(www[.]|)([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|)(([.]([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|))+)/i', $url, $matches) . PHP_EOL;

        if (count($matches) > 0) {
            $domain = $matches[0];
        }
    }

    return $domain;
}

function get_schema_by_uri(string $uri): ?string
{
    $schema = null;
    // $uri = "http://www.skfw.net/login?param=go";

    $matches = [];
    // ^
    // ([a-z]+):\/\/

    preg_match('/^([a-z]+):\/\//i', $uri, $matches) . PHP_EOL;
    if (count($matches) > 0)
    {
        $schema = $matches[0];
        $n = strlen($schema);

        // remove last chars '://' from schema!
        $schema = substr($schema, 0, $n - 3);
    }

    return $schema;
}

function get_param_by_uri(string $uri): ?string
{
    // $uri = "http://www.skfw.net/login?param=go";
    // return: param=go
    // /( 0_0)/

    $data = explode('?', $uri, limit: 2);
    if (count($data) > 1) return $data[1];
    return null;
}
// end of server utils
