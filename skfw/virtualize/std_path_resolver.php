<?php
namespace Skfw\Virtualize;

use Exception;
use PathSys;
use Skfw\Interfaces\IVirtStdPathResolver;

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

    /**
     * @throws Exception
     */
    public function __construct(string $path)
    {
        $this->_base_path = $this->_is_base_path($path);
        $this->_path_sys = $this->detect($path, $this->_base_path);

        if ($this->_path_sys == PathSys::WINDOWS) {
            $data = $this->_unpack_windows_path($path);
            $this->_origin_path = $this->_pack_windows_path($data, $this->_base_path);
            $this->_paths = $data["paths"];
            $this->_drive = $data["drive"];
            $this->_schema = null;
            $this->_domain = null;

        } else if ($this->_path_sys == PathSys::NETWORK) {
            $data = $this->_unpack_network_path($path);
            $this->_origin_path = $this->_pack_network_path($data, $this->_base_path);
            $this->_paths = $data["paths"];
            $this->_schema = $data["schema"];
            $this->_domain = $data["domain"];
            $this->_drive = null;

        } else if ($this->_path_sys == PathSys::POSIX) {
            $data = $this->_unpack_posix_path($path);
            $this->_origin_path = $this->_pack_posix_path($data, $this->_base_path);
            $this->_paths = $data["paths"];
            $this->_schema = null;
            $this->_drive = null;
            $this->_domain = null;

        } else {

            // throw error if path is not safe
            if (!is_safe_name($path)) throw new Exception("unknown path system");

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
    private function _is_base_path(string $path): bool
    {
        return preg_match("/^\//i", $path) or  // posix
            preg_match("/^([a-zA-Z]+):\/\//i", $path) or  // network, schema://
            preg_match("/^[a-zA-Z]:\\\\/i", $path);  // windows
        // preg_match("/^[a-zA-Z]:(\\|\/)/i", $path);  // windows, new way
    }

    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    /**
     * @throws Exception
     */
    private function _unpack_windows_path(string $path): array
    {
        // C:\\
        // C:\\Users\Guest\"My Document"\Games

        // get drive
        $drive = "C";  // default, image local disk on 'C' drive
        $data = explode(":", $path);
        $n = count($data);

        if ($n > 0) $drive = trim($data[0]);  // trimming empty space
        if ($n > 1) $path = $data[1];  // after get drive, turn it back path
        else $path = "";  // only took drive

        // removing all quotes
        $path = preg_replace("/['\"]/i", "", $path);

        // windows can take over like posix, but keep origin with backslash
        // C:\\Users\Guest
        // c:/Users/Guest

        $data = [];

        // like posix
        if (preg_match("/(\\[ ]|\/)/i", $path)) {

            // remove backslash
            $path = str_replace("\\", "", $path);

            // unpack path
            $data = explode("/", $path);

            // windows ordinary
        } else $data = explode("\\", $path);


        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");
        $data = [...$data];  // reset indexes

        // validation
        foreach ($data as $path)
        {
            if (!is_safe_name($path))
                throw new Exception("path is not valid");
        }

        // returning
        return [
            "drive" => $drive,
            "paths" => $data,
        ];
    }
    private function _pack_windows_path(array $data, bool $base = true): string
    {
        // collect data
        $drive = array_key_exists("drive", $data) ? $data["drive"] : "C";
        $paths = array_key_exists("paths", $data) ? $data["paths"] : [];

        // combine, windows ordinary
        return $base ? $drive . ":\\\\" . join("\\", $paths) : join("\\", $paths);
    }

    // TODO: have must be problem in network path, fix it!
    // network path can combine other path system like posix or windows
    // ex. file:///C:/Users/Guest/Documents/Public/index.html [Windows]
    /**
     * @throws Exception
     */
    private function _unpack_network_path(string $path): array
    {
        // file:///
        // file:///var/tmp/foo%20bar/book.log?param=go
        // ?param=go

        // get schema
        $schema = "file";  // default, image local schema on 'file' storage
        $data = explode( ":", $path);
        $n = count($data);

        if ($n > 0) $schema = trim($data[0]);  // trimming empty space
        if ($n > 1) $path = $data[1];  // after get schema, turn it back path
        else $path = "";  // only took schema

        // remove parameters
        $data = explode("?", $path);
        $path = count($data) > 0 ? $data[0] : "";  // only took param

        // decode special characters
        $path = urldecode($path);

        // TODO: fix it!
        // path contain domain tld, windows drive disk, posix like
        // schema: ^([a-zA-Z]+):\/\/
        // domain: [a-zA-Z0-9].+?[.]
        // posix: \\[ ]|\/ ~ no take single path
        // windows: [a-zA-Z]:(\\|\/) ~ only base ~ \\[ ]|\/ ~ posix like

        // check domain, check drive disk

        // unpack path
        $data = explode("/", $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");
        $data = [...$data];  // reset indexes

        // validation
        foreach ($data as $path)
        {
            if (!is_safe_name($path))
                throw new Exception("path is not valid");
        }

        // returning
        return [
            "schema" => $schema,
            "domain" => null,
            "paths" => $data,
        ];
    }
    private function _pack_network_path(array $data, bool $base = true): string
    {
        // collect data
        $schema = array_key_exists("schema", $data) ? $data["schema"] : "C";
        $paths = array_key_exists("paths", $data) ? $data["paths"] : [];

        // TODO: fix it!
        // path contain domain tld, windows drive disk, posix like
        // schema: ^([a-zA-Z]+):\/\/
        // domain: [a-zA-Z0-9].+?[.]
        // posix: \\[ ]|\/ ~ no take single path
        // windows: [a-zA-Z]:(\\|\/) ~ only base ~ \\[ ]|\/ ~ posix like

        // check domain, check drive disk

        // combine
        return $base ? $schema . "://" . join("/", $paths) : join("/", $paths);
    }

    /**
     * @throws Exception
     */
    private function _unpack_posix_path(string $path): array
    {
        // /var/tmp/foo\ bar/book.log

        // removing all quotes, look like windows?
        $path = preg_replace("/['\"]/i", "", $path);

        // remove backslash
        $path = str_replace("\\", "", $path);

        // unpack path
        $data = explode("/", $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");
        $data = [...$data];  // reset indexes

        // validation
        foreach ($data as $path)
        {
            if (!is_safe_name($path))
                throw new Exception("path is not valid");
        }

        // returning
        return [
            "paths" => $data,
        ];
    }
    private function _pack_posix_path(array $data, bool $base = true): string
    {
        // collect data
        $paths = array_key_exists("paths", $data) ? $data["paths"] : [];

        // combine
        return $base ? "/" . join("/", $paths) : join("/", $paths);
    }
    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    static public function detect(string $path, bool $base = true): PathSys
    {
        if ($base) {
            // it's not random pick,
            // posix -> network -> windows -> unknown

            // fun!
            if (preg_match("/^\//i", $path)) return PathSys::POSIX;

            // network can combine with posix, windows, or domain with tld main service
            // full spec: ^([a-zA-Z]+):\/\/(\/|[a-zA-Z]:(\\|\/)|[a-zA-Z0-9].+?[.])
            // short way: ^([a-zA-Z]+):\/\/

            else if (preg_match("/^([a-zA-Z]+):\/\//i", $path)) return PathSys::NETWORK;

            // windows can take well now with slash than backslash
            // windows can take well now with single backslash

            else if (preg_match("/^[a-zA-Z]:(\\|\/)/i", $path)) return PathSys::WINDOWS;

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
            if (preg_match("/(\\[ ]|\/)/i", $path))  return PathSys::POSIX;

            // only network, base neither true and false, network path must be base

            else if (preg_match("/^([a-zA-Z]+):\/\//i", $path)) return PathSys::NETWORK;

            // windows can take well with slash and backslash
            // the idea, windows path must be more selection on path
            // like slash or backslash for main idea

            else if (preg_match("/(\\|\/)/i", $path)) return PathSys::WINDOWS;

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
        ?string $drive = null,
        ?string $schema = null,
        bool $base = true,
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

            if ($path === ".") continue;  // passing
            else if ($path === "..") {
                $n = count($temp);
                if ($n > 0) {
                    $j = $n - 1;
                    $last = $temp[$j];
                    if ($last !== "..") array_pop($temp);  // last temp path is absolute path
                    else $temp[] = $path;  // last temp path is relative path
                }
                else if ($base) continue;  // no direct path on the top temp path
                else $temp[] = $path;

            } else $temp[] = $path;  // append new path on temp path
        }

        // data acquire, reset array indexes
        $paths = [...$temp];
        $ns = $drive !== null;
        $drive = $drive ?? "C";
        $schema = $schema ?? "file";  // local disk

        $n = count($paths);  // length of paths
        return match ($sys) {
            // only pain what you get, take alone!
            PathSys::WINDOWS => $base ? $drive . ":\\\\" . join("\\", $paths) : join("\\", $paths),

            // TODO: this network path not suitable for windows version, this it only combine with posix, fix it!
            // network, like posix, first path can look like a slash, show triple slash on network + posix
            PathSys::NETWORK => $schema . "://" . ($ns ? $drive . ":/" : "/") . join("/", $paths),

            // default on PHP, fun!
            PathSys::POSIX => $base ? "/" . join("/", $paths) : join("/", $paths),

            // $base or length of paths greater than 1, is not valid
            // maybe $paths took single path on unknown identify system are choice for path
            PathSys::UNKNOWN => $base or $n !== 1 ? throw new Exception("unknown path system") : $paths[0],
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
    public function system(): string
    {
        return $this->_path_sys->value;
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
