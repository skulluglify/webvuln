<?php
namespace Skfw\Cabbage;

use Exception;
use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;
use Skfw\Errors\Virtualize\VirtStdFileSizeDoNotMatch;
use Skfw\Errors\Virtualize\VirtStdFileTypeDoNotMatch;
use Skfw\Interfaces\Cabbage\IHttpBodyContent;
use Skfw\Interfaces\Cabbage\IHttpFile;
use Skfw\Interfaces\Cabbage\IHttpFileCollector;
use Skfw\Interfaces\Cabbage\IHttpHeader;
use Skfw\Interfaces\Cabbage\IHttpHeaderCollector;
use Skfw\Interfaces\Cabbage\IHttpInfoRequest;
use Skfw\Interfaces\Cabbage\IHttpParam;
use Skfw\Interfaces\Cabbage\IHttpParamCollector;
use Skfw\Interfaces\Cabbage\IHttpRequest;
use Skfw\Interfaces\Cabbage\IHttpStatusMessage;
use Skfw\Interfaces\IVirtStdPathResolver;
use Skfw\Virtualize\VirtStdContent;
use Stringable;

class HttpRequest implements Stringable, IHttpRequest
{
    private HttpInfoRequest $_info;
    private HttpStatusMessage $_status_message;
    private HttpHeaderCollector $_header_collector;
    private HttpParamCollector $_param_collector;
    private HttpFileCollector $_file_collector;
    private HttpBodyContent $_body_content;

    /**
     * @param array|null $server
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param VirtStdContent|null $content
     * @param int|null $file_chunk
     * @param int|null $file_max_size
     * @param int|null $stdin_max_size
     * @throws VirtStdFileSizeDoNotMatch
     * @throws VirtStdFileTypeDoNotMatch
     * @throws Exception
     */
    public function __construct(?array $server = null,
                                ?array $get = null,
                                ?array $post = null,
                                ?array $files = null,
                                ?VirtStdContent $content = null,
                                ?int $file_chunk = null,
                                ?int $file_max_size = null,
                                ?int $stdin_max_size = null,
    )
    {
        $this->_info = new HttpInfoRequest($server);  // get http information from client request!

        $status = $this->_info->status();  // get status code!
        $this->_status_message = new HttpStatusMessage($status);  // get message from status code!

        // object collectors!
        $this->_header_collector = new HttpHeaderCollector($server);
        $this->_param_collector = new HttpParamCollector($server, get: $get);

        // more files!
        $this->_file_collector = new HttpFileCollector($files, chunk: $file_chunk, max_size: $file_max_size);

        // body content is json unpack-able!
        $content_type = $this->_header_collector->header('content-type');
        $json_unpack = $content_type !== null && $content_type->shift() === 'application/json';

        $this->_body_content = new HttpBodyContent($content, post: $post, max_size: $stdin_max_size, json_unpack: $json_unpack);
    }

    public function __toString(): string
    {
        return self::class;  // get class name!
    }
    public function info(): IHttpInfoRequest
    {
        return $this->_info;
    }
    public function status_message(): IHttpStatusMessage
    {
        return $this->_status_message;
    }
    public function header_collector(): IHttpHeaderCollector
    {
        return $this->_header_collector;
    }
    public function param_collector(): IHttpParamCollector
    {
        return $this->_param_collector;
    }
    public function file_collector(): IHttpFileCollector
    {
        return $this->_file_collector;
    }
    public function body_content(): IHttpBodyContent
    {
        return $this->_body_content;
    }
    // merge methods from any implements!
    public function status(): ?HttpStatusCode
    {
        return $this->info()->status();
    }
    public function server_name(): string
    {
        return $this->info()->server_name();
    }
    public function server_port(): int
    {
        return $this->info()->server_port();
    }
    public function server_addr(): string
    {
        return $this->info()->server_addr();
    }
    public function client_addr(): string
    {
        return $this->info()->client_addr();
    }
    public function client_port(): int
    {
        return $this->info()->client_port();
    }
    public function server(): string
    {
        return $this->info()->server();
    }
    public function gateway(): string
    {
        return $this->info()->gateway();
    }
    public function scheme(): string
    {
        return $this->info()->scheme();
    }
    public function protocol(): string
    {
        return $this->info()->protocol();
    }
    public function root(): string
    {
        return $this->info()->root();
    }
    public function uri(): string
    {
        return $this->info()->uri();
    }
    public function size(): int
    {
        return $this->info()->size();
    }
    public function type(): string
    {
        return $this->info()->type();
    }
    public function method(): HttpMethod
    {
        return $this->info()->method();
    }
    public function fast_cgi_role(): string
    {
        return $this->info()->fast_cgi_role();
    }
    public function timestamp(): int
    {
        return $this->info()->timestamp();
    }

    /**
     * @throws Exception
     */
    public function path(): IVirtStdPathResolver
    {
        return $this->info()->path();
    }
    public function message(): string
    {
        return $this->status_message()->message();
    }
    public function headers(): array
    {
        return $this->header_collector()->headers();
    }
    public function header(string $name, int $case = 1): ?IHttpHeader
    {
        return $this->header_collector()->header($name, case: $case);
    }
    public function params(): array
    {
        return $this->param_collector()->params();
    }
    public function param(string $name, int $case = 1): ?IHttpParam
    {
        return $this->param_collector()->param($name, case: $case);
    }
    public function files(): array
    {
        return $this->file_collector()->files();
    }
    public function file(string $name, int $case = 1): ?IHttpFile
    {
        return $this->file_collector()->file($name, case: $case);
    }
    public function body(): array
    {
        return $this->body_content()->body();
    }
    public function json(): array
    {
        return $this->body_content()->json();
    }
}
