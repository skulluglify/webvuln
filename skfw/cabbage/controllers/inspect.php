<?php
namespace Skfw\Cabbage\Controllers;

use Exception;
use Generator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Skfw\Interfaces\Cabbage\Controllers\ICabbageInspectApp;
use Skfw\Interfaces\Cabbage\Controllers\ICabbageInspectAppController;
use Skfw\Tags\PathTag;
use Skfw\Virtualize\VirtStdPathResolver;

class CabbageInspectApp implements ICabbageInspectApp
{
    private string $_cwd;
    private string $_workdir;  // directory like controllers, models, or views!
    private int $_chunk = 64;  // set minimum data collate!

    /**
     * @param string|null $cwd
     * @param string $workdir
     * @throws Exception
     */
    public function __construct(?string $cwd = null, string $workdir = 'controllers')
    {
        // set default current work directory!
        if ($cwd !== null) $this->_cwd = $cwd;
        else
        {
            $base_dir = getcwd();
            if (empty($base_dir)) throw new Exception('base directory not give readable permission');
            $this->_cwd = $base_dir;
        }
        if (!is_safe_name($workdir)) throw new Exception('name of work directory is not safe');
        if (!is_dir($cwd . DIRECTORY_SEPARATOR . $workdir)) throw new Exception('var workdir is not a directory');
        $this->_workdir = $workdir;
    }

    /**
     * @param string $script
     * @return string|null
     */
    public static function get_namespace_from_script(string $script): ?string
    {
        $data = preg_split('/\\R/i', $script);
        foreach ($data as $line)
        {
            $temp = trim($line);
            //$temp = substr($temp, strlen($line));  // free up!
            if (preg_match('/^(<php\?(\s+)|)namespace(\s+).+?;/i', $temp))
            {

                $array = preg_split('/\s+/i', $temp);
                $array_length = count($array);

                if ($array_length > 0)
                {
                    $end = $array[$array_length - 1];
                    if (str_ends_with($end, ';'))
                    {
                        // remove semicolon!
                        return substr($end, 0, strlen($end) - 1);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param string $page
     * @return ReflectionClass|null
     */
    public function get_reflect_class(string $page): ?ReflectionClass
    {
        $cwd = $this->_cwd;
        $chunk = $this->_chunk;
        try {
            if (preg_match('/\w+/i', $page))
            {
                // ex. AdminController class!
                $controller = capitalize_each_word($page) . 'Controller';

                $script_name = $page . '.php';
                $script_src = $cwd . DIRECTORY_SEPARATOR . $this->_workdir . DIRECTORY_SEPARATOR . $script_name;

                if (file_exists($script_src))
                {
                    $temp = '';  // cache temporary script!
                    $stream = fopen($script_src, 'r');
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

                    require_once $script_src;  // add module script!
                    return new ReflectionClass($controller);
                }
            }
        } catch (Exception) {}

        return null;
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
     * @return Generator
     * @throws Exception
     * @yield DirectRouterController
     */
    public function get_direct_routers(string $page): Generator
    {
        $reflect = $this->get_reflect_class($page);
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method)
        {
            if ($method instanceof ReflectionMethod)
            {
                $opts = $method->isPublic() && !$method->isConstructor() && !$method->isDestructor();
                if ($opts)
                {
                    $attributes = $method->getAttributes(PathTag::class);
                    foreach ($attributes as $attribute)
                    {
                        if ($attribute instanceof ReflectionAttribute)
                        {
                            $args = $attribute->getArguments();
                            $tag = new PathTag(...$args);  // create new instance!
                            $path = new VirtStdPathResolver($tag->value());

                            // yield path sandbox and reflection method!
                            yield new DirectRouterController($path->sandbox(), $method);
                        }
                    }
                }
            }
        }
    }
}
