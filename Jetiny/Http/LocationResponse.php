<?php
namespace Jetiny\Http;

class LocationResponse extends Response
{
    public function __construct($url, $status = 302)
    {
        $this->url = $url;
        $this->stauts = $status;
    }
    
    public function send($app)
    {
        $app->header->setStatusCode($this->stauts);
        $app->header->set('Location', $this->url);
        $this->sendHeaders($app);
    }
    
    public $url;
    public $status;
}