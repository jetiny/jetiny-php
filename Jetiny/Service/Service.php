<?php
namespace Jetiny\Service;

class Service implements ServiceInterface
{
    public function setup($context) {
        $this->context = $context;
    }
    
    public function teardown(){
        $this->context = null;
    }
    protected $context;
}
