<?php
namespace Jetiny\Service;

class FormatService extends Service
{
    
    public function route($route, $req)
    {
        if ($formats = $route->option('format')) {
            $formats = explode('|', strtoupper($formats));
            
            $this->pareseQuery($req);
            $this->parseJsonp($req);
            $format = $req->format;
            
            if ($trusts = $this->context->config->get('format.trust')) {
                $trusts = explode('|', strtoupper($trusts));
                if (in_array($format, $trust)) {
                    return ;
                }
            }
            
            if (!in_array($format, $formats)) {
                \Jetiny\Base\Exception::throwError('err.invalid_request_format', 
                    'Invalid request format: ' . $req->format);
            }
        }
    }
    
    protected function pareseQuery($req)
    {
        if ($key = $this->context->config->get('format.query')) {
            if ($format = strtoupper($req->query->get($key))) {
                $req->format = $format;
            }
        }
    }
    
    protected function parseJsonp($req)
    {
        if ($jsonp = $this->context->config->get('format.jsonp')) {
            if ($jsonc = $req->query->get($jsonp)) {
                $req->format = 'JSONP';
                $req->jsonp = $jsonc;
            }
        }
    }
}