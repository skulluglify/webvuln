<?php
namespace Skfw\Interfaces\Cabbage;

interface IHttpRequest extends
    IHttpInfoRequest,
    IHttpStatusMessage,
    IHttpHeaderCollector,
    IHttpParamCollector,
    IHttpFileCollector,
    IHttpBodyContent
{
    public function info(): IHttpInfoRequest;
    public function status_message(): IHttpStatusMessage;
    public function header_collector(): IHttpHeaderCollector;
    public function param_collector(): IHttpParamCollector;
    public function file_collector(): IHttpFileCollector;
    public function body_content(): IHttpBodyContent;
}