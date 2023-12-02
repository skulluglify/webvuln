<?php
namespace Skfw\Cabbage\Controllers;

use Exception;
use Generator;
use PathSys;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Skfw\Cabbage\HttpRequest;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\Controllers\ICabbageInspectApp;
use Skfw\Interfaces\Cabbage\Controllers\ICabbageInspectAppController;
use Skfw\Interfaces\Cabbage\Controllers\ICabbageResourceController;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Tags\PathTag;
use Skfw\Virtualize\VirtStdPathResolver;

class CabbageInspectApp implements ICabbageInspectApp
{
    private string $_workdir;  // directory like controllers, models, or views!
    private int $_chunk = 64;  // set minimum data collate!

    /**
     * @param string|null $cwd
     * @param string $workdir
     * @throws Exception
     */
    public function __construct(?string $cwd = null, string $workdir = 'controllers')
    {
        if ($cwd === null)
        {
            $base = getcwd();
            if (empty($base)) throw new Exception("directory $base is not give readable permission");
            $cwd = $base;
        }
        if (!is_dir($cwd)) throw new Exception("directory $cwd is not found");
        if (!is_safe_name($workdir)) throw new Exception("name of $workdir is not safe");
        if (!is_dir($cwd . DIRECTORY_SEPARATOR . $workdir)) throw new Exception("directory $workdir is not found");
        $this->_workdir = $cwd . DIRECTORY_SEPARATOR . $workdir;
    }
    public function workdir(): string
    {
        return $this->_workdir;
    }
    /**
     * @param string $script
     * @return string|null
     */
    public static function get_namespace_from_script(string $script): ?string
    {
        // split code per line!
        $data = preg_split('/\\R/i', $script);
        foreach ($data as $line)
        {
            $matches = [];
            $temp = trim($line);

            // find namespace from code!
            if (preg_match('/namespace(\s+).+?;/i', $temp, $matches))
            {

                // found namespace!
                $found = $matches[0];
                $array = preg_split('/\s+/i', $found);

                // get name from combination 'namespace' code!
                if (count($array) > 0)
                {
                    $name = array_pop($array);
                    return substr($name, 0, strlen($name) - 1);
                }
            }
        }

        // not found!
        return null;
    }

    /**
     * @param string $page
     * @return string|null
     */
    public function get_reflect_class(string $page): ?string
    {
        $chunk = $this->_chunk;
        $workdir = $this->_workdir;
        try {

            if (!safe_file_name($page)) throw new Exception("name of $page is not valid");
            if (!is_file($workdir . DIRECTORY_SEPARATOR . $page . '.php')) throw new Exception("file $page is not found");

            if (preg_match('/\w+/i', $page))
            {
                // ex. AdminController class!
                $controller = capitalize_each_word($page) . 'Controller';
                $script = $workdir . DIRECTORY_SEPARATOR . $page . '.php';

                if (is_file($script))  // check is file!
                {
                    $temp = '';  // cache temporary script!
                    $stream = fopen($script, 'r');
                    $namespace = null;

                    while (!feof($stream))
                    {
                        $data = fread($stream, $chunk);
                        $temp .= $data;

                        if (preg_match('/\\R/i', $temp))
                        {
                            $namespace = $this->get_namespace_from_script($temp);
                            if (!empty($namespace)) break;
                        }
                    }

                    $controller = !empty($namespace) ? '\\'.$namespace.'\\'.$controller : $controller;

                    require_once $script;  // add module script!
                    return $controller;
                }
            }
        } catch (Exception) {}

        return null;
    }
}

class CabbageResourceController implements ICabbageResourceController
{
    private array $_middlewares;
    private VirtStdPathResolver $_prefix;

    /**
     * @throws Exception
     */
    public function __construct(VirtStdPathResolver|string $prefix, array $middlewares)
    {
        $prefix = $prefix instanceof VirtStdPathResolver ? $prefix : new VirtStdPathResolver($prefix);
        $this->_prefix = $prefix->sandbox(PathSys::POSIX);
        $this->_middlewares = [];
        foreach ($middlewares as $middleware)
        {
            if ($middleware instanceof IMiddleware)
                $this->_middlewares[] = $middleware;
        }
    }
    /**
     * @return IMiddleware[]
     */
    public function middlewares(): array
    {
        return $this->_middlewares;
    }
    public function prefix(): VirtStdPathResolver
    {
        return $this->_prefix;
    }
}

class CabbageInspectAppController extends CabbageInspectApp implements ICabbageInspectAppController
{
    /**
     * @param string|null $cwd
     * @param string $workdir
     * @throws Exception
     */
    public function __construct(?string $cwd = null, string $workdir = 'controllers')
    {
        parent::__construct($cwd, $workdir);
    }

    /**
     * @param string $page
     * @return ICabbageResourceController
     * @throws Exception
     */
    public function get_resource_from_class(string $page): ICabbageResourceController
    {
        try {
            $class = $this->get_reflect_class($page);

            $obj = new $class;
            $reflect = new ReflectionClass($obj);

            $prefix = '';
            $middlewares = [];
            $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
            foreach ($methods as $method)
            {
                if (!$method->isAbstract() && !$method->isConstructor() && !$method->isDestructor()) {
                    $name = $method->getName();
                    if ($name === 'middlewares') {
                        $result = $method->invoke($obj);
                        if (!empty($result) && is_array($result)) $middlewares = $result;
                    } else
                    if ($name === 'prefix') {
                        $result = $method->invoke($obj);
                        if (!empty($result) && is_string($result)) $prefix = $result;
                    }
                }
            }

            // result!
            return new CabbageResourceController($prefix, $middlewares);
        } catch (Exception) {}

        // passing error!
        return new CabbageResourceController('', []);
    }
    /**
     * @param string $page
     * @return Generator
     * @throws Exception
     * @yield DirectRouterController
     */
    public function get_routers_from_class(string $page): Generator
    {
        $class = $this->get_reflect_class($page);

        $obj = new $class;
        $reflect = new ReflectionClass($obj);

        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method)
        {
            if (!$method->isAbstract() && !$method->isConstructor() && !$method->isDestructor())
            {
                $attributes = $method->getAttributes(PathTag::class);
                foreach ($attributes as $attribute)
                {
                    // same as PathTag object class!
                    if ($attribute->getName() === PathTag::class)
                    {
                        $args = $attribute->getArguments();
                        $tag = new PathTag(...$args);  // create new instance!
                        $path = new VirtStdPathResolver($tag->value());
                        $path = $path->sandbox();  // sandbox

                        // yield path sandbox and reflection method!
                        $closure = fn(HttpRequest $req): ?HttpResponse => $method->invoke($obj, $req);
                        yield new DirectRouterController($path, $closure);
                    }
                }
            }
        }
    }
}
