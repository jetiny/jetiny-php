<?php

namespace Jetiny;

class Router
{
    
    public function setOptions($value) {
        $this->_options = array_replace_recursive($this->_options, $value);
        if (isset($this->_options['routers'])) {
            $this->_routers = $this->_options['routers'];
            unset($this->_options['routers']);
        }
    }
    
    public function parse($req) {
        //路由模式
        $mode = $this->_options['mode'];
        $queryMode = strpos($mode, 'query') !== FALSE;
        $pathMode  = strpos($mode, 'path' ) !== FALSE;
        $routeMode = strpos($mode, 'route' ) !== FALSE;
        
        //选项
        $class = $this->_options['class'];
        $method = $this->_options['method'];
        $default = 'index';
        // 路径
        $path = $req->path;
        if ($routeMode) { // 路由匹配模式
            $route = $this->match($path, strtolower($req->method));
        }
        else if ($queryMode && $path === '/') { // query模式 ?c=class&m=method
            $class  = $req->request($class,  $default);
            $method = $req->request($method, $default);
            $route = [ 'handle' => $class.':'.$method ];
        }
        else if ($pathMode) {// path模式, /class/method
            $class = $method = $default;
            $paths = explode('/', preg_replace('/(^\/|\/$)/', '', $path));
            $n = count($paths);
            if ($n > 1) {
                $method = $paths[1];
            }
            if ($n >0) {
                $class = $paths[0];
            }
            $route = [ 'handle' => $class.':'.$method ];
        }
        if ($route && is_string($route['handle'])) {
            list($class, $method) = explode(':', $route['handle']);
            $route['class']  = $class;
            $route['method'] = $method;
            unset($route['handle']);
        }
        return $route;
    }
    function __call($method, $args) {
        if (in_array($method, static::$_httpMethods)){
            array_unshift($args, $method);
            return call_user_func_array(array(&$this, 'when'), $args);
        }
    }
    public function when($methods, $path, $handle) {
        $r = [
            $path,   //路径或正则
            $handle, //类或闭包
        ];
        if (is_array($methods)) { // 批量模式
            foreach ($methods as $method) {
                $this->_routers[$method][] = $r;
            }
        } else {
            $this->_routers[$methods][] = $r;
        }
    }
    public function match($path, $method) {
        if (isset($this->_routers[$method])) {
            foreach($this->_routers[$method] as &$it){
                if (!isset($it['match'])) {
                    $match = $it['match'] = $this->createRegexp($it[0]);
                } else {
                    $match = $it['match'];
                }
                $val = $match($path);
                if ($val !== NULL) {
                    return [
                        'handle' => $it[1],
                        'args' => $val,
                    ];
                }
            }
        }
        if ($method !== 'any') {
            return $this->match($path, 'any');
        }
    }
    protected function createRegexp ($route){ // /test:abc/:def => /testa/bc
        $keys = [];
        $re = preg_replace_callback("/(\/)?:(\w+)?/", function($matches)use(&$keys){
            $slash = $matches[1] ? '/' : '';
            $keys[] = $matches[2];
            return "{$slash}([^/]+)";
        }, $route);
        $re = preg_replace("/([\/]+)/", '\\/', $re);
        $char = $route[strlen($route) -1];
        if ($char !== '/'){ // in case no / at end
            $re .= '(?:\\/)?';
        }
        $re = '/^' . $re . '$/';
        return function ($str) use(&$keys, &$re){
           if (preg_match($re, $str, $out)) {
                $ma = array();
                array_shift($out);
                foreach($out as $k => $v){
                    $ma[$keys[$k]] = $v;
                }
                return $ma;
           }
        };
    }
    static protected $_httpMethods = array(
        'get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'any'
    );
    protected $_routers = [];
    protected $_options = [
        'mode' => 'query|path|route',
        'class'  => 'c',
        'method' => 'm',
    ];
}
