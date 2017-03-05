<?php
namespace Jetiny\Base;

//类加载器
class Factory
{
    
    public function __get($name)
    {
        if (isset($this->_classes[$name]))
            return $this->_classes[$name];
        if ($object = $this->createObject($name)) {
            return $this->_classes[$name] = $object;
        }
    }
    
    // create App\Action\UserAction with $name = 'user'
    public function createObject($name)
    {
        $class = $this->expandClass($name);
        if ($this->classExists($class)) {
            return new $class;
        }
    }
    
    public function expandClass($class)
    {
        // user => User
        $class = ucwords($class);
        // User => UserAction
        if ($suffix = $this->option('class_suffix')) {
            $class .= $suffix;
        }
        // UserAction => App\Action\UserAction
        if ($namespace = $this->option('namespace')) {
            $class = $namespace . $class;
        }
        return $class;
    }
    
    // auto load class
    protected function classExists($class) {
        if (class_exists($class))
            return TRUE;
        return FALSE !== @class_implements($class, true);
    }
    
    public function option($key) {
        if (isset($this->_options[$key]))
            return  $this->_options[$key];
    }
    
    public function setOptions($options) {
        if ($options) {
            $this->_options = array_replace($this->_options, $options);
        }
    }
    
    protected $_options = [];
    protected $_classes = [];
}