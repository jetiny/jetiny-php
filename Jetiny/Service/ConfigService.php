<?php
namespace Jetiny\Service;

class ConfigService extends \Jetiny\Base\Config
{
    
    public function setup($context) {
        $this->context = $context;
        $this->set('ndebug', TRUE);
        $this->loadFile($context->configFile());
    }
    
    public function loadFile($file) {
        if (is_readable($file)){
            $data = include $file;
            $this->extend($data);
        }
    }
    
    public function extend($key, $value = null) {
        parent::extend($key, $value);
        if ($services = $this->peek('services')) {
            $this->context->registerServices($services);
        }
        if ($preloads = $this->peek('preloads')) {
            $this->context->createServices($preloads);
        }
    }
    
}
