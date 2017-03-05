<?php
namespace Jetiny\Service;

class KenelService
{
    
    public function __get($service) {
        if ($instance = $this->getService($service)) {
            return $this->{$service} = $instance;
        }
    }
    
    public function getService($name) {
        $service = null;
        if ( isset($this->services[$name]) ) {
            return $this->services[$name];
        } 
        if (isset($this->servicePool[$name])) {    // simple => 'SimpleService'
            $service = $this->servicePool[$name];
        } else {
            foreach($this->servicePool as $key => $it) {    // ['SimpleService'] => simple
                if ($this->extractServiceName($it) == $name) {
                    $service = $it;
                }
            }
        }
        \Jetiny\Base\Event::emit('kenel.require.service', [$name, &$service]);
        return $this->createService($name, $service);
    }
    
    public function hasService($name) {
        return  isset($this->services[$name]);
    }
    
    protected function extractServiceName($name) {
        // namespace\SimpleService => simple
        // namespace\SimpleAppService => simpleApp
        if (FALSE !== ($pos = strripos($name, '\\'))) {        // remove namespace
            $name = substr($name, $pos + 1, strlen($name));
        }
        if (FALSE !== ($pos = strripos($name, 'Service'))) {   // chop 'Service'
            $name = substr($name, 0 , $pos);
        }
        return lcfirst($name);
    }
    
    public function createService($name, $service = NULL) {
        if (is_string($service)) { // instance as classname
            $service = new $service;
        }
        if (is_string($name) && !$service) { // name as classname
            $service = new $name;
        }
        if (!is_string($name)){
            $name = get_class($service);
        }
        
        $name = $this->extractServiceName($name);
        
        $this->services[$name] = $service;
        if (method_exists($service, 'setup')) {
            $service->setup($this);
        }
        return $this->services[$name];
    }
    
    public function createServices($map) {
        foreach($map as $name => $service) {
            $this->createService($name, $service);
        }
    }
    
    public function registerService($name, $service = NULL) {
        if (is_null($service)) {
            $service = $name;
            $name = $this->extractServiceName($service);
        }
        $this->servicePool[$name] = $service;
    }
    
    public function registerServices($map) {
        foreach($map as $name => $service) {
            if (is_numeric($name)) {
                $this->registerService($service);
            } else {
                $this->registerService($name, $service);
            }
        }
    }
    
    public function setup() {
        if ($this->_setup)
            return false;
        $this->_setup = TRUE;
        $this->servicePool = array_merge($this->preloads, $this->servicePool);
        $this->createServices($this->preloads);
        return true;
    }
    
    protected function teardown() {
        $this->walkService(function($it){
            if (method_exists($it, 'teardown'))
                $it->teardown();
        });
    }
    
    public function walkService($fn) {
        array_walk($this->services, $fn);
    }
    
    protected $_setup = FALSE;
    protected $services = [];
    protected $servicePool = [];
    protected $preloads = [];
}