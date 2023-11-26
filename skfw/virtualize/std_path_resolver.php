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

        } else if ($this->_path_sys == PathSys::NETWORK) {
            $data = $this->_unpack_network_path($path);
            $this->_origin_path = $this->_pack_network_path($data, $this->_base_path);
            $this->_paths = $data["paths"];
            $this->_schema = $data["schema"];
            $this->_drive = null;

        } else if ($this->_path_sys == PathSys::POSIX) {
            $data = $this->_unpack_posix_path($path);
            $this->_origin_path = $this->_pack_posix_path($data, $this->_base_path);
            $this->_paths = $data["paths"];
            $this->_schema = null;
            $this->_drive = null;

        } else {
            // $this->_origin_path = "";
            // $this->_paths = [];
            // $this->_schema = null;
            // $this->_drive = null;
            throw new Exception("unknown path system");

        }

        $this->_size = count($this->_paths);
    }
    public function __toString(): string
    {
        return $this->_origin_path;
    }
    private function _is_base_path(string $path): bool
    {
        return preg_match("/^\//i", $path) or
            preg_match("/^([a-zA-Z]+):\/\//i", $path) or
            preg_match("/^[a-zA-Z]:\\\\/i", $path);
    }
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

        // unpack path
        $data = explode("\\", $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");

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

        // combine
        return $base ? $drive . ":\\\\" . join("\\", $paths) : join("\\", $paths);
    }
    private function _unpack_network_path(string $path): array
    {
        // file://
        // file://var/tmp/foo%20bar/book.log?param=go
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

        // unpack path
        $data = explode("/", $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");

        // returning
        return [
            "schema" => $schema,
            "paths" => $data,
        ];
    }
    private function _pack_network_path(array $data, bool $base = true): string
    {
        // collect data
        $schema = array_key_exists("schema", $data) ? $data["schema"] : "C";
        $paths = array_key_exists("paths", $data) ? $data["paths"] : [];

        // combine
        return $base ? $schema . "://" . join("/", $paths) : join("/", $paths);
    }
    private function _unpack_posix_path(string $path): array
    {
        // /var/tmp/foo\ bar/book.log

        // remove backslash
        $path = str_replace("\\", "", $path);

        // unpack path
        $data = explode("/", $path);

        // cleaning path, trimming empty space
        $data = array_map(fn(string $v): string => trim($v), $data);
        $data = array_filter($data, fn(string $v): bool => $v !== "");

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
    // class method, no need initial class object
    static public function detect(string $path, bool $base = true): PathSys
    {
        if ($base) {
            if (preg_match("/^\//i", $path)) return PathSys::POSIX;
            else if (preg_match("/^([a-zA-Z]+):\/\//i", $path)) return PathSys::NETWORK;
            else if (preg_match("/^[a-zA-Z]:\\\\/i", $path)) return PathSys::WINDOWS;
            else return PathSys::UNKNOWN;
        } else {
            // posix can do with char '\' like '/foo\ bar/tmp'
            if (str_contains($path, "/"))  return PathSys::POSIX;
            else if (preg_match("/^([a-zA-Z]+):\/\//i", $path)) return PathSys::NETWORK;
            else if (str_contains($path, "\\")) return PathSys::WINDOWS;
            else return PathSys::UNKNOWN;
        }
    }
    // class method, no need initial class object
    /**
     * @throws Exception
     */
    static public function pack(
        array $paths,
        string $drive = "C",
        string $schema = "file",
        bool $base = true,
        PathSys $sys = PathSys::POSIX): string
    {
        return match ($sys) {
            PathSys::WINDOWS => $base ? $drive . ":\\\\" . join("\\", $paths) : join("\\", $paths),
            PathSys::NETWORK => $base ? $schema . "://" . join("/", $paths) : join("/", $paths),
            PathSys::POSIX => $base ? "/" . join("/", $paths) : join("/", $paths),
            PathSys::UNKNOWN => throw new Exception("unknown path system"),
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
    public function size(): int
    {
        return $this->_size;
    }
}
