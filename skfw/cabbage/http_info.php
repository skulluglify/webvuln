<?php
namespace Skfw\Cabbage;

use Exception;
use PathSys;
use Skfw\Enums\HttpMethod;
use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpInfoRequest;
use Skfw\Virtualize\VirtStdPathResolver;

class HttpInfoRequest implements IHttpInfoRequest
{
    private HttpStatusCode $_redirect_status;
    private string $_server_name;
    private int $_server_port;
    private string $_server_addr;
    private string $_remote_addr;
    private int $_remote_port;
    private string $_server_software;
    private string $_gateway_interface;
    private string $_request_scheme;
    private string $_server_protocol;
    private string $_document_root;
    private string $_document_uri;
    private int $_content_length;
    private string $_content_type;
    private HttpMethod $_request_method;
    private string $_fast_cgi_role;
    private int $_request_time;
    private VirtStdPathResolver $_path;

    /**
     * @throws Exception
     */
    function __construct(?array $server = null)
    {
        // default assign object!
        $server = $server ?? $_SERVER;

        // make it harder than your pennies!
        $redirect_status = $server['REDIRECT_STATUS'];  // value: 200
        $status_code = intval($redirect_status);  // try parsing!
        $status = HttpStatusCode::tryFrom($status_code) ?? HttpStatusCode::IM_A_TEAPOT;
        $this->_redirect_status = $status;

        $this->_server_name = $server['SERVER_NAME'];  // value: _

        $server_port = $server['SERVER_PORT'];  // value: 80
        $this->_server_port = intval($server_port);  // try parsing!

        $this->_server_addr = $server['SERVER_ADDR'];  // value: ::1
        $this->_remote_addr = $server['REMOTE_ADDR'];  // value: ::1

        $remote_port = $server['REMOTE_PORT'];  // value: 55682
        $this->_remote_port = intval($remote_port);  // try parsing!

        $this->_server_software = $server['SERVER_SOFTWARE'];  // value: nginx/1.24.0
        $this->_gateway_interface = $server['GATEWAY_INTERFACE'];  // value: CGI/1.1
        $this->_request_scheme = $server['REQUEST_SCHEME'];  // value: http
        $this->_server_protocol = $server['SERVER_PROTOCOL'];  // value: HTTP/1.1
        $this->_document_root = $server['DOCUMENT_ROOT'];  // value: /var/www/html
        $this->_document_uri = $server['REQUEST_URI'] ?? $server['DOCUMENT_URI'];  // value: /index.php

        $content_length = $server['CONTENT_LENGTH'] ?? '0';  // value: 40
        $this->_content_length = intval($content_length);  // try parsing!

        $this->_content_type = $server['CONTENT_TYPE'] ?? 'text/plain';  // value: application/json

        $request_method = $server['REQUEST_METHOD'];  // value: POST
        $method = strtoupper($request_method);  // set upper case of request method!
        $this->_request_method = HttpMethod::tryFrom($method) ?? HttpMethod::GET;

        $this->_fast_cgi_role = $server['FCGI_ROLE'];  // value: RESPONDER

        $request_time = $server['REQUEST_TIME'];  //1700694440
        $this->_request_time = intval($request_time);  // try parsing!

        // path from remote based!
        $this->_path = new VirtStdPathResolver($this->_document_uri);
    }

    public function status(): ?HttpStatusCode
    {
        return $this->_redirect_status;
    }
    public function server_name(): string
    {
        return $this->_server_name;
    }
    public function server_port(): int
    {
        return $this->_server_port;
    }
    public function server_addr(): string
    {
        return $this->_server_addr;
    }
    public function client_addr(): string
    {
        return $this->_remote_addr;
    }
    public function client_port(): int
    {
        return $this->_remote_port;
    }
    public function server(): string
    {
        return $this->_server_software;
    }
    public function gateway(): string
    {
        return $this->_gateway_interface;
    }
    public function scheme(): string
    {
        return $this->_request_scheme;
    }
    public function protocol(): string
    {
        return $this->_server_protocol;
    }
    public function root(): string
    {
        return $this->_document_root;
    }
    public function uri(): string
    {
        return $this->_document_uri;
    }
    public function size(): int
    {
        return $this->_content_length;
    }
    public function type(): string
    {
        return $this->_content_type;
    }
    public function method(): HttpMethod
    {
        return $this->_request_method;
    }
    public function fast_cgi_role(): string
    {
        return $this->_fast_cgi_role;
    }
    public function timestamp(): int
    {
        return $this->_request_time;
    }

    /**
     * @throws Exception
     */
    public function path(): string
    {
        return VirtStdPathResolver::pack($this->_path->paths(), sys: PathSys::POSIX);
    }
}