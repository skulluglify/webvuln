<?php
namespace Skfw\Virtualize;

use Exception;
use PathSys;
use Skfw\Interfaces\IVirtStdPathResolver;

// check domain on regex
// ([a-z]+):\/\/([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|)(([.]([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|))+)(\/|$)

class VirtStdPathResolver implements IVirtStdPathResolver
{
    private bool $_base_path;
    private string $_origin_path;
    private array $_paths;
    private int $_size;
    private PathSys $_path_sys;
    private ?string $_drive;  // windows path only
    private ?string $_schema;  // net path only
    private ?string $_domain;  // net path only, domain way
    //private bool $_win_path_v2;

    /**
     * @throws Exception
     */
    public function __construct(string $path, bool $win_path_v2 = false)
    {
        //$this->_win_path_v2 = $win_path_v2;
        $this->_base_path = $this->_is_base_path($path);
        $this->_path_sys = $this->detect($path, $this->_base_path);

        if ($this->_path_sys == PathSys::WINDOWS) {
            $data = $this->_unpack_windows_path($path);
            $this->_origin_path = $win_path_v2 ? $this->_pack_windows_path_v2($data, $this->_base_path) : $this->_pack_windows_path($data, $this->_base_path);
            $this->_paths = $data['paths'];
            $this->_drive = $data['drive'];
            $this->_schema = null;
            $this->_domain = null;

        } else if ($this->_path_sys == PathSys::NETWORK) {
            $data = $this->_unpack_network_path($path);
            $this->_origin_path = $this->_pack_network_path($data, $this->_base_path);
            $this->_paths = $data['paths'];
            $this->_schema = $data['schema'];
            $this->_domain = $data['domain'];
            $this->_drive = $data['drive'];

        } else if ($this->_path_sys == PathSys::POSIX) {
            $data = $this->_unpack_posix_path($path);
            $this->_origin_path = $this->_pack_posix_path($data, $this->_base_path);
            $this->_paths = $data['paths'];
            $this->_schema = null;
            $this->_drive = null;
            $this->_domain = null;

        } else {

            // throw error if path is not safe
            if (!is_safe_name($path)) throw new Exception('unknown path system');

            // paths, maybe took single path
            $this->_path_sys = PathSys::UNKNOWN;
            $this->_origin_path = $path;
            $this->_paths = [$path];
            $this->_schema = null;
            $this->_drive = null;
            $this->_domain = null;
        }

        $this->_size = count($this->_paths);
    }
    public function __toString(): string
    {
        return $this->_origin_path;
    }
    static private function _is_base_path(string $path): bool
    {
        return preg_match('/^\//i', $path) or  // posix
            preg_match('/^([a-z]+):\/\//i', $path) or  // network, schema://
            preg_match('/^[A-Z]:(\\\\|\/)/i', $path);  // windows
        // preg_match('/^[A-Z]:(\\|\/)/i', $path);  // windows, new way
    }

    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    /**
     * @throws Exception
     */
    static private function _unpack_windows_path(string $path): array
    {
        // C:\\
        // C:\\Users\Guest\'My Document'\Games

        // shrinking multiple empty space for any path!
        //$path = preg_replace('/\s+/i', ' ', $path);

        // get drive
        $drive = 'C';  // default, image local disk on 'C' drive
        $data = explode(':', $path);
        $n = count($data);

        if ($n > 0) $drive = trim($data[0]);  // trimming empty space
        if ($n > 1) $path = $data[1];  // after get drive, turn it back path
        else $path = '';  // only took drive

        // removing all quotes
        $path = preg_replace('/[\'\"]/i', '', $path);

        // windows can take over like posix, but keep origin with backslash
        // C:\\Users\Guest
        // c:/Users/Guest

        $data = [];

        // like posix
        if (preg_match('/(\\[ ]|\/)/i', $path)) {

            // remove backslash
            $path = str_replace('\\', '', $path);

            // unpack path
            $data = explode('/', $path);

            // windows ordinary
        } else $data = explode('\\', $path);


        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== '');
        $data = [...$data];  // reset indexes

        // validation
        foreach ($data as $path)
        {
            if (!is_safe_name($path))
                throw new Exception('path is not valid');
        }

        // upper
        $drive = strtoupper($drive);

        // returning
        return [
            'drive' => $drive,
            'paths' => $data,
        ];
    }
    static private function _pack_windows_path(array $data, bool $base = true): string
    {
        // collect data
        $drive = array_key_exists('drive', $data) ? $data['drive'] : 'C';
        $paths = array_key_exists('paths', $data) ? $data['paths'] : [];

        // wrapping path contains empty space with double quotes!
        $paths = array_map(fn(string $path): string => trim($path), $paths);  // trimming path
        $paths = array_map(fn(string $path): string => str_contains($path, ' ') ? '"' . $path . '"' : $path, $paths);

        // combine, windows ordinary
        return $base ? strtoupper($drive) . ':\\\\' . join('\\', $paths) : join('\\', $paths);
    }

    static private function _pack_windows_path_v2(array $data, bool $base = true): string
    {
        // collect data!
        $drive = array_key_exists('drive', $data) ? $data['drive'] : 'C';

        // combine drive with lower case and path like posix!
        $path = ($base ? $drive . ':' : '') . self::_pack_posix_path($data, $base);

        // windows can't handle backslash for naming directory like posix!
        return str_replace("\ ", " ", $path);
    }
    // TODO: have must be problem in network path, fix it!
    // network path can combine other path system like posix or windows
    // ex. file:///C:/Users/Guest/Documents/Public/index.html [Windows]
    /**
     * @throws Exception
     */
    static private function _unpack_network_path(string $path): array
    {
        // empty space not allowed on network path
        //$path = preg_replace('/\s+/i', '', $path);

        // file:///
        // file:///var/tmp/foo%20bar/book.log?param=go
        // ?param=go
        $domain = get_domain_by_uri($path);
        $schema = get_schema_by_uri($path);

        if ($schema === null)
            throw new Exception('unable get schema from uri');

        // remove header from network path!
        $header = $domain !== null ? $schema . '://' . $domain : $schema . '://';
        $path = substr($path, strlen($header), strlen($path));

        // remove any param on network path!
        $data = explode('?', $path, limit: 2);
        $path = count($data) > 0 ? $data[0] : '';
        $path = urldecode($path);

        // maybe is a local storage!
        $data = [];
        if ($domain === null)
        {
            $data = match (self::detect($path)) {
                PathSys::WINDOWS => self::_unpack_windows_path($path),
                PathSys::POSIX => self::_unpack_posix_path($path),
                default => throw new Exception('unknown system path'),
            };
        } else
        {
            $data = self::_unpack_posix_path($path);
        }

        $drive = array_key_exists('drive', $data) ? $data['drive'] : null;
        $paths = array_key_exists('paths', $data) ? $data['paths'] : [];

        // returning
        return [
            'schema' => $schema,
            'drive' => $drive,
            'domain' => $domain,
            'paths' => $paths,
        ];
    }

    /**
     * @throws Exception
     */
    static private function _pack_network_path(array $data, bool $base = true): string
    {
        // network path must be true!
        if (!$base) throw new Exception('network path must be true');

        // collect data
        $schema = array_key_exists('schema', $data) ? $data['schema'] : 'file';
        $domain = array_key_exists('domain', $data) ? $data['domain'] : null;
        $drive = array_key_exists('drive', $data) ? $data['drive'] : null;
        //$paths = array_key_exists('paths', $data) ? $data['paths'] : [];
        //$paths = array_map(fn(string $path): string => trim($path), $paths);  // trimming path

        // TODO: fix it!
        // path contain domain tld, windows drive disk, posix like
        // schema: ^([a-zA-Z]+):\/\/
        // domain: [a-zA-Z0-9].+?[.]
        // posix: \\[ ]|\/ ~ no take single path
        // windows: [a-zA-Z]:(\\|\/) ~ only base ~ \\[ ]|\/ ~ posix like

        // check domain, check drive disk
        $address = $domain ?? '';

        if ($domain === null && $drive !== null) $address .= self::_pack_windows_path_v2($data, $base);
        else $address .= self::_pack_posix_path($data, $base);

        // network path can't handle backslash or any empty space for naming directory!
        $address = str_replace('\\', '', $address);
        $address = preg_replace('/\s+/i', '+', $address);

        // combine
        return $schema . '://' . $address;
    }

    /**
     * @throws Exception
     */
    static private function _unpack_posix_path(string $path): array
    {
        // /var/tmp/foo\ bar/book.log

        // shrinking multiple empty space for any path!
        //$path = preg_replace('/\s+/i', ' ', $path);

        // remove quotes!
        $path = preg_replace('/[\'\"]/i', '', $path);

        // remove backslash
        $path = str_replace('\\', '', $path);

        // unpack path
        $data = explode('/', $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== '');
        $data = [...$data];  // reset indexes

        // validation
        foreach ($data as $path)
        {
            if (!is_safe_name($path))
                throw new Exception('path is not valid');
        }

        // returning
        return [
            'paths' => $data,
        ];
    }
    static private function _pack_posix_path(array $data, bool $base = true): string
    {
        // collect data
        $paths = array_key_exists('paths', $data) ? $data['paths'] : [];

        // replace all empty space directory with backslash
        $paths = array_map(fn(string $path): string => trim($path), $paths);  // trimming path
        $paths = array_map(fn(string $path): string => str_replace(' ', '\\ ', $path), $paths);

        // combine
        return $base ? '/' . join('/', $paths) : join('/', $paths);
    }
    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    static public function detect(string $path, bool $base = true): PathSys
    {
        if ($base) {
            // it's not random pick,
            // posix -> network -> windows -> unknown

            // fun!
            if (preg_match('/^\//i', $path)) return PathSys::POSIX;

            // network can combine with posix, windows, or domain with tld main service
            // full spec: ^([a-zA-Z]+):\/\/(\/|[a-zA-Z]:(\\|\/)|[a-zA-Z0-9].+?[.])
            // short way: ^([a-zA-Z]+):\/\/

            else if (preg_match('/^([a-z]+):\/\//i', $path)) return PathSys::NETWORK;

            // windows can take well now with slash than backslash
            // windows can take well now with single backslash

            else if (preg_match('/^[A-Z]:(\\\\|\/)/i', $path)) return PathSys::WINDOWS;

            // single path, like file or directory name only

            else return PathSys::UNKNOWN;
        } else {
            // it's not random pick,
            // posix -> network -> windows -> unknown

            // TODO: maybe path took only file or directory name with special char for chaining
            // ex. My\ Documents, My\ Downloads, My\ Videos
            // posix can do with char '\' like '/foo\ bar/tmp'
            // must be check with full, \\[ ] for empty space

            // welcome to posix family!
            if (preg_match('/(\\[ ]|\/)/i', $path))  return PathSys::POSIX;

            // only network, base neither true and false, network path must be base

            else if (preg_match('/^([a-z]+):\/\//i', $path)) return PathSys::NETWORK;

            // windows can take well with slash and backslash
            // the idea, windows path must be more selection on path
            // like slash or backslash for main idea

            else if (preg_match('/(\\|\/)/i', $path)) return PathSys::WINDOWS;

            // single path, like file or directory name only

            else return PathSys::UNKNOWN;
        }
    }
    // class method, no need initial class object
    /**
     * @throws Exception
     */
    static public function pack(
        array $paths,
        string $drive = 'C',  // local disk
        string $schema = 'file',  // local storage
        bool $base = true,
        bool $win_path_v2 = false,
        PathSys $sys = PathSys::POSIX): string
    {
        // abs path mix per path
        // remove any trilling
        // multiple any path supported
        // ./../
        $temp = [];

        foreach ($paths as $path)
        {
            // trimming path, safe method
            $path = trim($path);

            if ($path === '.') continue;  // passing
            else if ($path === '..') {
                $n = count($temp);
                if ($n > 0) {
                    $j = $n - 1;
                    $last = $temp[$j];
                    if ($last !== '..') array_pop($temp);  // last temp path is absolute path
                    else $temp[] = $path;  // last temp path is relative path
                }
                else if ($base) continue;  // no direct path on the top temp path
                else $temp[] = $path;

            } else $temp[] = $path;  // append new path on temp path
        }

        // data acquire, reset array indexes
        $paths = [...$temp];

        $n = count($paths);  // length of paths
        return match ($sys) {
            // only pain what you get, take alone!
            PathSys::WINDOWS => $win_path_v2 ? self::_pack_windows_path_v2([
                'drive' => $drive,
                'paths' => $paths,
            ]) : self::_pack_windows_path([
                'drive' => $drive,
                'paths' => $paths,
            ]),

            // TODO: this network path not suitable for windows version, this it only combine with posix, fix it!
            // network, like posix, first path can look like a slash, show triple slash on network + posix
            PathSys::NETWORK => self::_pack_network_path([
                'schema' => $schema,
                'drive' => $drive,
                'paths' => $paths,
            ], $base),

            // default on PHP, fun!
            PathSys::POSIX => self::_pack_posix_path([
                'paths' => $paths,
            ], $base),

            // $base or length of paths greater than 1, is not valid
            // maybe $paths took single path on unknown identify system are choice for path
            PathSys::UNKNOWN => $base or $n !== 1 ? throw new Exception('unknown path system') : $paths[0],
        };
    }
    public function is_base_path(): bool
    {
        return $this->_base_path;
    }
    public function paths(): array
    {
        return $this->_paths;  // unsafe for using
    }
    public function system(): PathSys
    {
        return $this->_path_sys;
    }
    public function drive(): string
    {
        return $this->_drive;  // unsafe for using
    }
    public function schema(): string
    {
        return $this->_schema;  // unsafe for using
    }
    public function domain(): string
    {
        return $this->_domain;  // unsafe for using
    }
    public function size(): int
    {
        return $this->_size;
    }
}
