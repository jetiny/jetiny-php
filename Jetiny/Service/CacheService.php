<?php
namespace Jetiny\Service;
use Jetiny\Base\Exception;

class CacheService extends \Jetiny\Cache\CacheProxy
{
    
    public function setup($context)
    {
        $this->context = $context;
        $config = $context->config;
        $this->_confPool = $config->get('cache.pool', []);
        $default = $config->get('cache.default');
        if (!$default)
            Exception::throwMessage('Please makesure the config for cache is exists.');
        $ndebug = $config->get('ndebug');
        $exp = NULL;
        try {
            $handler = $this->createHandler($default);
            $this->setCacher($handler);
        } catch (\Exception $e) { // 
            if ($ndebug)
                $exp = $e;
            else  // throw in debug mode
                throw $e;
        }
        if ($exp) { //从缓存池中找到可用的
            $handler = $this->createValidHandler();
            if ($handler instanceof \Exception) {
                throw $exp;
            }
            $this->setCacher($handler);
            $exp = NULL;
        }
    }
    
    protected function createValidHandler()
    {
        $exp = NULL;
        foreach ($this->_confPool as $opts) {
            $name = $opts['name'];
            if (isset($this->_maps[$name]))
                return $this->_maps[$name];
            else if (array_key_exists($name, $this->_maps[$name]))
                continue;
            try {
                return $this->createHandler($opts);
            } catch (\Exception $e) {
                $exp = $e;
            }
        }
        return $exp;
    }
    
    protected function createHandlerByName($name)
    {
        if (array_key_exists($name, $this->_maps[$name]))
            return $this->_maps[$name];
        foreach ($this->_confPool as $opts) {
            if ($opts['name'] == $name) {
                return $this->createHandler($opts);
            }
        }
    }
    
    protected function createHandler($options)
    {
        $name  = $options['name'];
        $class = isset($options['class']) ? $options['class'] : NULL;
        if (is_null($class) && $name) {
            $class = '\\Jetiny\\Cache\\' .ucwords($name) . 'Cache';
        }
        $handler = new $class;
        $handler->setup($options);
        return $this->_maps[$name] = $handler;
    }
    
    protected $_maps = [];
    protected $_confPool = [];
}