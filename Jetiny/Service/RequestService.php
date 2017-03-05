<?php
namespace Jetiny\Service;

class RequestService extends \Jetiny\Http\Request
{
    public function setup($context)
    {
        $this->setOptions($context->config->peek('request'));
    }
}