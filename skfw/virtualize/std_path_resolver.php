<?php
namespace Skfw\Virtualize;

use Exception;
use PathSys;
use Skfw\Interfaces\IVirtStdPathResolver;

// check domain on regex
// ([a-z]+):\/\/([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|)(([.]([a-zA-Z0-9]+)(((-|_)[a-zA-Z0-9]+)+|))+)(\/|$)

class VirtStdPathResolver implements IVirtStdPathResolver
{
    private bool $_is_base_dir;
    private string $_origin_path;
    private array $_values;
    private int $_size;
    private PathSys $_path_sys;
    private ?string $_drive;  // windows path only
    private ?string $_schema;  // net path only
    private ?string $_domain;  // net path only, domain way
    //private bool $_win_path_v2;
    private bool $_is_sandbox;

    /**
     * @throws Exception
     */
    public function __construct(string $path, bool $sandbox = false, bool $win_path_v2 = false)
    {
        // auto direct current directory!
        $path = $path !== '' ? $path : '.';

        //$this->_win_path_v2 = $win_path_v2;
        $this->_is_base_dir = $this->_is_base_dir($path);
        $this->_is_sandbox = $sandbox;

        // detection base path!
        $this->_path_sys = $this->detect($path, $this->_is_base_dir);

        if ($this->_path_sys == PathSys::WINDOWS) {
            $data = $this->_unpack_windows_path($path);
            $this->_origin_path = $win_path_v2 ? $this->_pack_windows_path_v2($data, $this->_is_base_dir) : $this->_pack_windows_path($data, $this->_is_base_dir);
            $this->_values = $data['values'];
            $this->_drive = $data['drive'];
            $this->_schema = null;
            $this->_domain = null;

        } else if ($this->_path_sys == PathSys::NETWORK) {
            $data = $this->_unpack_network_path($path);
            $this->_origin_path = $this->_pack_network_path($data, $this->_is_base_dir);
            $this->_values = $data['values'];
            $this->_schema = $data['schema'];
            $this->_domain = $data['domain'];
            $this->_drive = $data['drive'];

        } else if ($this->_path_sys == PathSys::POSIX) {
            $data = $this->_unpack_posix_path($path);
            $this->_origin_path = $this->_pack_posix_path($data, $this->_is_base_dir);
            $this->_values = $data['values'];
            $this->_schema = null;
            $this->_drive = null;
            $this->_domain = null;

        } else {

            // throw error if path is not safe
            if (!is_safe_name($path)) throw new Exception('unknown path system');

            // values, maybe took single path
            $this->_path_sys = PathSys::UNKNOWN;
            $this->_origin_path = $path;
            $this->_values = [$path];
            $this->_schema = null;
            $this->_drive = null;
            $this->_domain = null;
        }

        $this->_size = count($this->_values);
    }
    public function __toString(): string
    {
        return $this->_origin_path;
    }
    private static function _is_base_dir(string $path): bool
    {
        return preg_match('/^\//i', $path) or  // posix
            preg_match('/^([a-z]+):\/\//i', $path) or  // network, schema://
            preg_match('/^[A-Z]:(\\\\|\/)/i', $path);  // windows
        // preg_match('/^[A-Z]:(\\\\|\/)/i', $path);  // windows, new way
    }

    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    /**
     * @throws Exception
     */
    private static function _unpack_windows_path(string $path): array
    {
        // C:\\
        // C:\\Users\Guest\'My Document'\Games

        // shrinking multiple empty space for any path!
        //$path = preg_replace('/[ ]+/i', ' ', $path);

        // get drive
        $drive = 'C';  // default, image local disk on 'C' drive
        $data = explode(':', $path);
        $n = count($data);

        if ($n > 0) $drive = trim($data[0]);  // trimming empty space
        if ($n > 1) $path = $data[1];  // after get drive, turn it back path
        else $path = '';  // only took drive

        // removing all quotes!
        $path = preg_replace('/[\'\"]/i', '', $path);

        // windows can take over like posix, but keep origin with backslash
        // C:\\Users\Guest
        // c:/Users/Guest

        //$data = [];

        // like posix
        if (preg_match('/(\/|\\\ )/i', $path)) {

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
            'values' => $data,
        ];
    }
    private static function _pack_windows_path(array $data, bool $base = true): string
    {
        // collect data
        $drive = array_key_exists('drive', $data) ? $data['drive'] : 'C';
        $values = array_key_exists('values', $data) ? $data['values'] : [];

        // wrapping path contains empty space with double quotes!
        $values = array_map(fn(string $path): string => trim($path), $values);  // trimming path
        $values = array_map(fn(string $path): string => str_contains($path, ' ') ? '"' . $path . '"' : $path, $values);

        // Combine, Windows Path!
        // Windows ignores double backslash! (Universal Naming Convention Path)
        return $base ? strtoupper($drive) . ':\\' . join('\\', $values) : join('\\', $values);
    }

    private static function _pack_windows_path_v2(array $data, bool $base = true): string
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
    private static function _unpack_network_path(string $path): array
    {
        // empty space not allowed on network path
        //$path = preg_replace('/[ ]+/i', '', $path);

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
        //$data = [];
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
        $values = array_key_exists('values', $data) ? $data['values'] : [];

        // returning
        return [
            'schema' => $schema,
            'drive' => $drive,
            'domain' => $domain,
            'values' => $values,
        ];
    }

    /**
     * @throws Exception
     */
    private static function _pack_network_path(array $data, bool $base = true): string
    {
        // network path must be true!
        if (!$base) throw new Exception('network path must be true');

        // collect data
        $schema = array_key_exists('schema', $data) ? $data['schema'] : 'file';
        $domain = array_key_exists('domain', $data) ? $data['domain'] : null;
        $drive = array_key_exists('drive', $data) ? $data['drive'] : null;
        //$values = array_key_exists('values', $data) ? $data['values'] : [];
        //$values = array_map(fn(string $path): string => trim($path), $values);  // trimming path

        // have domain, it s not file local storage, goto network! (must bet securing)
        $schema = $schema === 'file' && $domain !== null ? 'https' : $schema;

        // TODO: fix it!
        // path contain domain tld, windows drive disk, posix like
        // schema: ^([a-zA-Z]+):\/\/
        // domain: [a-zA-Z0-9].+?[.]
        // posix: \\[ ]|\/ ~ no take single path
        // windows: [a-zA-Z]:(\\\\|\/) ~ only base ~ \\[ ]|\/ ~ posix like

        // check domain, check drive disk
        $address = $domain ?? '';

        if ($domain === null && $drive !== null) $address .= self::_pack_windows_path_v2($data, $base);
        else $address .= self::_pack_posix_path($data, $base);

        // network path can't handle backslash or any empty space for naming directory!
        $address = str_replace('\\', '', $address);
        $address = preg_replace('/[ ]+/i', '+', $address);

        // combine
        return $schema . '://' . $address;
    }

    /**
     * @throws Exception
     */
    private static function _unpack_posix_path(string $path): array
    {
        // /var/tmp/foo\ bar/book.log

        // shrinking multiple empty space for any path!
        //$path = preg_replace('/[ ]+/i', ' ', $path);

        // removing all quotes!
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
            'values' => $data,
        ];
    }
    private static function _pack_posix_path(array $data, bool $base = true): string
    {
        // collect data
        $values = array_key_exists('values', $data) ? $data['values'] : [];

        // replace all empty space directory with backslash
        $values = array_map(fn(string $path): string => trim($path), $values);  // trimming path
        $values = array_map(fn(string $path): string => str_replace(' ', '\\ ', $path), $values);

        // combine
        return $base ? '/' . join('/', $values) : join('/', $values);
    }
    // TODO: windows path can write like posix, fix it!
    // ex. c:/Users/Guest/Downloads [Windows][Valid]
    public static function detect(string $path, bool $base = true): PathSys
    {
        if ($base) {
            // it's not random pick,
            // posix -> network -> windows -> unknown

            // fun!
            if (preg_match('/^\//i', $path)) return PathSys::POSIX;

            // network can combine with posix, windows, or domain with tld main service
            // full spec: ^([a-zA-Z]+):\/\/(\/|[a-zA-Z]:(\\\\|\/)|[a-zA-Z0-9].+?[.])
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
            if (preg_match('/(\/|\\\ )/i', $path))  return PathSys::POSIX;

            // only network, base neither true and false, network path must be base

            else if (preg_match('/^([a-z]+):\/\//i', $path)) return PathSys::NETWORK;

            // windows can take well with slash and backslash
            // the idea, windows path must be more selection on path
            // like slash or backslash for main idea

            else if (preg_match('/(\\\\|\/)/i', $path)) return PathSys::WINDOWS;

            // single path, like file or directory name only

            else return PathSys::UNKNOWN;
        }
    }
    // class method, no need initial class object
    /**
     * @throws Exception
     */
    public static function pack(
        array $values,
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

        foreach ($values as $path)
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
        $values = [...$temp];

        $n = count($values);  // length of values
        return match ($sys) {
            // only pain what you get, take alone!
            PathSys::WINDOWS => $win_path_v2 ? self::_pack_windows_path_v2([
                'drive' => $drive,
                'values' => $values,
            ], base: $base) : self::_pack_windows_path([
                'drive' => $drive,
                'values' => $values,
            ], base: $base),

            // TODO: this network path not suitable for windows version, this it only combine with posix, fix it!
            // network, like posix, first path can look like a slash, show triple slash on network + posix
            PathSys::NETWORK => self::_pack_network_path([
                'schema' => $schema,
                'drive' => $drive,
                'values' => $values,
            ], base: $base),

            // default on PHP, fun!
            PathSys::POSIX => self::_pack_posix_path([
                'values' => $values,
            ], base: $base),

            // $base or length of values greater than 1, is not valid
            // maybe $values took single path on unknown identify system are choice for path
            PathSys::UNKNOWN => throw new Exception('unknown system path'),
        };
    }
    public function is_base_dir(): bool
    {
        return $this->_is_base_dir;
    }
    public function values(): array
    {
        return $this->_values;  // unsafe for using
    }
    public function system(): PathSys
    {
        return $this->_path_sys;
    }
    public function drive(): ?string
    {
        return $this->_drive;  // unsafe for using
    }
    public function schema(): ?string
    {
        return $this->_schema;  // unsafe for using
    }
    public function domain(): ?string
    {
        return $this->_domain;  // unsafe for using
    }
    public function size(): int
    {
        return $this->_size;
    }

    /**
     * @throws Exception
     */
    public function join(string ...$values): self
    {
        $wrapper = self::class;
        foreach ($values as $path)
        {
            $resolve = new $wrapper($path);
            foreach ($resolve->values() as $p)
            {
                $this->_values[] = $p;
                $this->_size += 1;
            }
        }

        // update origin path!
        $this->_origin_path = $this->repack();
        return $this;
    }
    public function path(): string
    {
        // make it suitable for php in any cases!
        if (!$this->is_base_dir()) return join(DIRECTORY_SEPARATOR, $this->_values);  // no base!
        $drive = $this->_drive !== null ? $this->_drive . ':' . DIRECTORY_SEPARATOR : '/';
        return $drive . join(DIRECTORY_SEPARATOR, $this->_values);
    }

    /**
     * @param array|null $values
     * @param string|null $drive
     * @param string|null $schema
     * @param bool|null $base
     * @param PathSys|null $sys
     * @return string
     * @throws Exception
     */
    public function repack(?array $values = null, ?string $drive = null, ?string $schema = null, ?bool $base = null, ?PathSys $sys = null): string
    {
        // default system using!
        $values = $values ?? $this->values();
        $drive = $drive ?? $this->drive() ?? 'C';
        $schema = $schema ?? $this->schema() ?? 'file';
        $system = $this->system() !== PathSys::UNKNOWN ? $this->system() : PathSys::POSIX;

        // modifier schema, if domain is not null!
        $domain = $this->domain();
        if ($domain !== null)
        {
            $values = [$domain, ...$values];
            $schema = $schema !== 'file' ? $schema : 'https';  // change default value!
        }

        $base = $base ?? $this->is_base_dir();
        $sys = $sys ?? $system;

        return self::pack($values, drive: $drive, schema: $schema, base: $base, sys: $sys);
    }
    /**
     * @throws Exception
     */
    public function sandbox(?PathSys $sys = null): IVirtStdPathResolver
    {
        $wrapper = self::class;

        // default system using!
        $values = $this->values();
        $drive = $this->drive() ?? 'C';
        $schema = $this->schema() ?? 'file';
        $system = $this->system() !== PathSys::UNKNOWN ? $this->system() : PathSys::POSIX;

        // modifier schema, if domain is not null!
        $domain = $this->domain();
        if ($domain !== null)
        {
            $values = [$domain, ...$values];
            $schema = $schema !== 'file' ? $schema : 'https';  // change default value!
        }

        $pack = self::pack($values, drive: $drive, schema: $schema, base: true, sys: $sys ?? $system);
        return new $wrapper($pack, sandbox: true);
    }
    /**
     * @return bool
     */
    public function is_sandbox(): bool
    {
        // check is sandbox or not!
        return $this->_is_sandbox;
    }
    /**
     * @return bool
     */
    public function is_network(): bool
    {
        // check is sandbox or not!
        return $this->_path_sys === PathSys::NETWORK;
    }
    /**
     * @return bool
     */
    public function is_posix(): bool
    {
        // check is sandbox or not!
        return $this->_path_sys === PathSys::POSIX;
    }

    /**
     * @param string|IVirtStdPathResolver $path
     * @param bool $sandbox
     * @param bool $relative
     * @param int $case
     * @return bool
     * @throws Exception
     */
    public function equal(string|IVirtStdPathResolver $path, bool $sandbox = false, bool $relative = false, int $case = 1): bool
    {
        $wrapper = self::class;  // wrapper object class itself!
        $other = $path instanceof IVirtStdPathResolver ? $path : new $wrapper($path);
        $other = $sandbox && !$other->is_sandbox() ? $other->sandbox() : $other;  // take a once!

        $left = !$relative ? $this->absolute() : $this->relative();
        $right = !$relative ? $other->absolute() : $other->relative();

        $left_values = $left->values();
        $right_values = $right->values();

        $left_length = $left->size();
        $right_length = $right->size();

        if ($left_length !== $right_length) return false;

        for ($i = 0; $i < $left_length; $i++)
        {
            $left_value = $left_values[$i];
            $right_value = $right_values[$i];

            if (!str_comp_case($left_value, $right_value, $case)) return false;
        }

        return true;
    }

    /**
     * @param int $case
     * @return IVirtStdPathResolver
     * @throws Exception
     */
    public function absolute(int $case = 1): IVirtStdPathResolver
    {
        $wrapper = self::class;

        $is_sandbox = $this->is_sandbox();
        $is_base_dir = $this->is_base_dir();
        $is_network = $this->is_network();

        // network must be base dir
        // sandbox mode can't determine base directory with this class!
        if ($is_sandbox or $is_network) return $this;
        if ($is_base_dir) return $this;  // is base directory!

        $base_dir = getcwd();
        if (empty($base_dir)) throw new Exception('base directory not give readable permission');
        $base = new VirtStdPathResolver($base_dir);

        if ($this->size() > 0)
        {

            $idx = 0;
            $found = false;
            $values = $this->values();
            $temp = [];

            // find equal path and merging!
            foreach ($base->values() as $path)
            {
                $value = $values[$idx];
                if (str_comp_case($path, $value, $case))
                {
                    $found = true;
                    $idx += 1;  // next value of path!
                    if ($this->size() <= $idx) break;
                }
                if ($found) break;  // snapped!
                $temp[] = $path;  // append, on iteration!
            }

            // combine next path on values!
            array_snap($temp, $values, $idx, $this->size());

            // re-packing path on virtual std path resolver!
            $system = $this->system() !== PathSys::UNKNOWN ? $this->system() : $base->system();
            $sys = $system !== PathSys::UNKNOWN ? $system : PathSys::POSIX;
            $pack = $this->repack($temp, base: true, sys: $sys);
            return new $wrapper($pack);
        }

        return $base;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function dirname(): string
    {
        $n = $this->size();
        $values = $this->values();
        if ($n > 0)
        {
            $values = array_slice($values, 0, $n - 1);

            // repeat initial values!
            if (count($values) > 0) return $this->repack($values);
        }
        return $this->is_base_dir() ? '/' : '.';
    }
    /**
     * @return string
     * @throws Exception
     */
    public function basename(): string
    {
        $n = $this->size();
        $values = $this->values();
        if ($n > 0) return $this->repack([$values[$n - 1],], base: false);
        return $this->is_base_dir() ? '/' : '.';
    }
    /**
     * @param int $case
     * @return IVirtStdPathResolver
     * @throws Exception
     */
    public function relative(int $case = 1): IVirtStdPathResolver
    {
        $wrapper = self::class;

        $is_sandbox = $this->is_sandbox();
        $is_base_dir = $this->is_base_dir();
        $is_network = $this->is_network();

        // network must be base dir
        // sandbox mode can't determine base directory with this class!
        $n = $this->size();  // get length of values!
        if ($is_sandbox or $is_network) return $n > 0 ? new $wrapper($this->values()[$n - 1]) : $this;
        if (!$is_base_dir) return $this;  // is not base directory!

        $base_dir = getcwd();
        if (empty($base_dir)) throw new Exception('base directory not give readable permission');
        $base = new VirtStdPathResolver($base_dir);

        // get absolute path!
        $absolute = $this->absolute($case);

        if ($absolute->size() > 0)
        {
            $idx = 0;
            $temp = [];
            $values = $absolute->values();
            foreach ($base->values() as $i => $path)
            {
                if ($i < $absolute->size())
                {
                    $value = $values[$idx];  // get current value!
                    if (str_comp_case($path, $value, $case)) $idx += 1;
                }
            }

            // combine continue path values into temporary path!
            array_snap($temp, $values, $idx, $absolute->size());

            // re-packing path on virtual std path resolver!
            $system = $this->system() !== PathSys::UNKNOWN ? $this->system() : $absolute->system();
            $sys = $system !== PathSys::UNKNOWN ? $system : PathSys::POSIX;
            $pack = $this->repack($temp, base: false, sys: $sys);
            return new $wrapper($pack);
        }

        return $this;
    }

    /**
     * @param bool|null $base
     * @return string
     * @throws Exception
     */
    public function posix(?bool $base = null): string
    {
        $base = $base ?? $this->is_base_dir();
        return $this->repack($this->values(), base: $base, sys: PathSys::POSIX);
    }

    /**
     * @param bool|null $base
     * @return string
     * @throws Exception
     */
    public function network(?bool $base = null): string
    {
        $base = $base ?? $this->is_base_dir();
        return $this->repack($this->values(), base: $base, sys: PathSys::NETWORK);
    }

    /**
     * @param bool|null $base
     * @return string
     * @throws Exception
     */
    public function windows(?bool $base = null): string
    {
        $base = $base ?? $this->is_base_dir();
        return $this->repack($this->values(), base: $base, sys: PathSys::WINDOWS);
    }
}
