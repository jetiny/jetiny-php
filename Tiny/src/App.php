<?php

namespace Jetiny;

class App
{
    
    static public function instance()
    {
        static $_instance;
        if (!$_instance) {
            $_instance = new static;
        }
        return $_instance;
    }
    
    public function setOptions($data, $override = TRUE) {
        if ($override) {
            $this->_options  =$data;
        } else {
            $this->_options = array_replace_recursive($this->_options, $data);
        }
    }
    
    public function option($module) {
        return isset($this->_options[$module]) ? $this->_options[$module] : NULL;
    }
    
    public function run() {
        $req = $this->request;
        $res = $this->response;
        $router = $this->router;
        
    }
    
    // 获取单例对象
    public function __get($module) {
        $mod =  new $this->_modules[$module];
        if (method_exists($mod, 'setOptions') && ($opts = $this->option($module))) {
            $mod->setOptions($opts);
        }
        if (method_exists($mod, 'setup')) {
            $mod->setup($this);
        }
        return $this->{$module} = $mod;
    }
    
    // 模块映射
    protected $_modules = [
        'request'  => '\Jetiny\Request',
        'response' => '\Jetiny\Response',
        'router'   => '\Jetiny\Router',
    ];
    protected $_options = [
        'namespace' => '',
        'suffix' => 'Action',
    ];
}
