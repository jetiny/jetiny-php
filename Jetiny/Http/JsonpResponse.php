<?php
namespace Jetiny\Http;

class JsonpResponse extends JsonResponse
{
    
    public function contentType()
    {
        return \Jetiny\Base\Util::getMimeType('JS');
    }
    
    public function __toString()
    {
        $jsonp = $this->app->request->jsonp;
        return $jsonp . "(" . json_encode($this) . ")";
    }
}