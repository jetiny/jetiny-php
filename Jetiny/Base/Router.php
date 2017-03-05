<?php
namespace Jetiny\Base;

use Jetiny\Base\Route;
use Jetiny\Base\Exception;

class Router
{
    
    public function createRouters($routers)
    {
        if (is_array($routers)) {
            foreach($routers as $v){
                $r = Route::create($v, $this->_caseInsensitive);
                $name = $r->option('name');
                if ($name) {
                    $this->_routers[$name] = $r;
                } else {
                    $this->_routers[] = $r;
                }
            }
        }
    }
    
    public function createRoute($methods, $path, $data = null)
    {
        $r = new Route($path, $this->_caseInsensitive);
        $r->setMethods($methods);
        if (is_callable($data)){
            $data = array('closure' =>$data);
        }
        $r->setOptions($data);
        return $r;
    }
    
    public function when($methods, $path, $name, $data = null)
    {
        if (!is_string($name)) {
            $data = $name;
            $name = null;
        }
        if (is_callable($data)){
            $data = array('closure' =>$data);
        }
        if ($name){
            $data['name'] = $name;
        }
        $r = $this->createRoute($methods, $path, $data);
        if (is_string($name))
            $this->_routers[$name] = $r;
        else
            $this->_routers[] = $r;
        return $r;
    }
    
    public function match($path, $method = null)
    {
        foreach($this->_routers as $k => $it) {
            if (is_array($m = $it->matchUrl($path))) {
                if (!$method || $it->accept($method))
                    return array($it, $m);
            }
        }
    }
    
    public function matchAll($path, $method = null)
    {
        $r = array();
        foreach($this->_routers as $k => $it) {
            if (is_array($m = $it->matchUrl($path))) {
                if (!$method || $it->accept($method))
                    $r[] = array($it, $m);
            }
        }
        return count($r) ? $r : null;
    }
    
    public function url($name, $data)
    {
        if (isset($this->_routers[$name])) {
            $it = $this->_routers[$name];
            if (!is_array($data)) {
                $data = func_get_args();
                array_shift($data);
            }
            $r = $it->makeUrl($data);
            if (is_string($r))
                return $r;
            Exception::throwError('err.make_url', $name);
        }
    }
    
    static protected $_httpMethods = array(
        'get', 'post', 'put', 'patch', 'delete', 'head', 'options'
    );
    function __call($method, $args)
    {
        if (in_array($method, static::$_httpMethods)){
            array_unshift($args, $method);
            return call_user_func_array(array(&$this, 'when'), $args);
        }
    }
    
    protected $_routers = array();
    protected $_caseInsensitive;
}
