<?php
namespace Skfw\Cabbage;

use Skfw\Enums\HttpStatusCode;
use Skfw\Interfaces\Cabbage\IHttpHeader;
use Skfw\Interfaces\Cabbage\IHttpResponse;

class HttpResponse implements IHttpResponse
{
    private string $_data;
    private HttpStatusCode $_status;
    private bool $_sending;
    private array $_headers;
    private string $_protocol;

    /**
     * @param string $data
     * @param HttpStatusCode $status
     * @param IHttpHeader[] $headers
     */
    public function __construct(string $data = '', HttpStatusCode $status = HttpStatusCode::OK, array $headers = [])
    {
        $server = $_SERVER;  // new assign!

        $this->_data = $data;
        $this->_status = $status;
        $this->_sending = false;
        $this->_protocol = $server['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        $this->_headers = $headers;
        // header('HTTP/1.1 200 OK');
    }
    public function __toString(): string
    {
        return self::class;
    }
    public function sender(): void
    {
        // header code!
        $code = $this->_status->value;
        $message = new HttpStatusMessage($this->_status);
        $protocol = $this->_protocol;  // get protocol, fallback from proxy!
        header($protocol . ' ' . $code . ' ' . $message);

        // default headers!
        date_default_timezone_set('UTC');
        header('Date: ' . date(DATE_RFC2822));
        header('Server: Cabbage/1.2.0 (Skfw-Net; ' . PHP_OS .')');
        header('Vary: Accept, Accept-Encoding, Accept-Language, Cache-Control, Connection, Cookie, Host, User-Agent');

        // determine content!
        $type = 'text/html; charset=UTF-8';  // maybe?
        $length = strlen($this->_data);  // get length of data!
        header('Content-Type: ' . $type);  // default type!
        header('Content-Length: ' . $length);  // set length of content!

        // sending headers!
        $headers = $this->_headers;
        $fn_safe_puts_csv = fn(string $v): string => preg_match('/(,|\s)/i', $v) ? '"' . $v . '"' : $v;
        foreach ($headers as $header)
        {
            if ($header instanceof IHttpHeader)
            {
                $key = $header->name();
                $values = $header->values();
                $res = implode(',', array_map($fn_safe_puts_csv, $values));
                header($key . ': ' . $res);  // key: csv!
            }
        }

        // sending data!
        //echo $this->_data;  // sending data into web page!
        $stream = fopen('php://output', 'wb');
        //fseek($stream, 0);
        fwrite($stream, $this->_data);
        fclose($stream);

        $this->_sending = true;  // sending signal!
        $this->_data = '';  // free up!
    }
    public function sending(): bool
    {
        return $this->_sending;
    }
    public function data(): string
    {
        return $this->_data;
    }
    public function status(): HttpStatusCode
    {
        return $this->_status;
    }
    public function headers(): array
    {
        return $this->_headers;
    }
    public function protocol(): string
    {
        return $this->_protocol;
    }
}
