<?php
namespace Jetiny\Http;

class DataResponse implements ResponseInterface
{
    
    public function setup($context)
    {
        $this->app = $context;
        return $this;
    }
    
    public function __construct($error, $data,  $message) {
        $this->error = $error;
        $this->message = $message;
        $this->data = $data;
    }
    
    public function json()
    {
        return (new \Jetiny\Http\JsonResponse($this->error, $this->data, $this->message))->setup($this->app);
    }
    
    public function jsonp()
    {
        return (new \Jetiny\Http\JsonpResponse($this->error, $this->data, $this->message))->setup($this->app);
    }
    
}