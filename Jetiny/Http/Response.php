<?php
namespace Jetiny\Http;

class Response implements ResponseInterface
{
    
    public function setup($context)
    {
        $this->app = $context;
        return $this;
    }
    
    public function send()
    {
        while(ob_get_level())
            ob_end_clean();
        ob_start(); // 'ob_gzhandler'
        echo $this;
        $len = ob_get_length();
        $header = $this->app->header;
        if ($contentType  = $this->contentType()) {
            $header->set('Content-Type', $this->contentType());
            $header->set('Content-Length', $len);
        }
        $this->sendHeaders();
        ob_end_flush();
        flush();
    }
    
    public function sendHeaders()
    {
        $app = $this->app;
        if (!headers_sent()) {
            if (isset($app->header)) {
                $app->header->send();
            }
            if (isset($app->cookie)) {
                $app->cookie->send();
            }
        }
    }
    
    public function contentType()
    {
        return \Jetiny\Base\Util::getMimeType('HTML');
    }
    
    
    protected $app;
}