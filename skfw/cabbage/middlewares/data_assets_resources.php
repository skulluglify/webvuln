<?php
namespace Skfw\Cabbage\Middlewares;

use Closure;
use Exception;
use finfo;
use PathSys;
use Skfw\Abstracts\cabbage\MiddlewareAbs;
use Skfw\Cabbage\HttpHeader;
use Skfw\Cabbage\HttpResponse;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpResponse;
use Skfw\Interfaces\Cabbage\IMiddleware;
use Skfw\Virtualize\VirtStdPathResolver;

class DataAssetsResourcesMiddleware extends MiddlewareAbs implements IMiddleware
{
    private VirtStdPathResolver $_directory_resource;
    private array $_pages;

    /**
     * @throws Exception
     */
    public function __construct(string $directory, array $pages = ['index.html', 'index.php'])
    {
        parent::__construct();  // binding next handler!
        $this->_directory_resource = new VirtStdPathResolver($directory);
        $this->_pages = $pages;  // auto direct to initial pages!
    }

    /**
     * @throws Exception
     */
    public function handler(IHttpRequest $request): ?IHttpResponse
    {
        $path = $request->path();
        $resolver = new VirtStdPathResolver($path);

        // fake root directory
        $path = VirtStdPathResolver::pack($resolver->values(), base: true, sys: $resolver->system());
        $resolver = new VirtStdPathResolver($path);  // safe path!

        // combine base directory with request uri path!
        $resolver = $this->_directory_resource->join(...$resolver->values());  // path combine!
        $path = $resolver->path();

        // try pages!
        if (is_file($path)) return $this->_render($path);
        else if (is_dir($path))
        {
            $pages = $this->_pages;
            foreach ($pages as $page)
            {
                $p = $path . '/' . $page;
                if (is_file($p)) return $this->_render($p);
            }
        }

        return null;
    }
    private function _render($path): ?HttpResponse
    {
        $content = file_get_contents($path);
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $content_type = $file_info->buffer($content);
        return !empty($content) ? new HttpResponse($content, headers: [
            new HttpHeader('content-type', values: [$content_type]),
        ]) : null;
    }
}