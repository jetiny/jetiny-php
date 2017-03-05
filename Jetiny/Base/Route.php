<?php
namespace Jetiny\Base;

class Route
{
    public function __construct($path, $caseInsensitive = null)
    {
        $this->_path   = $path ;
        $this->_regexp = $this->createRegexp($path, $caseInsensitive);
    }
    
    protected function createRegexp ($path, $caseInsensitive){
    	$r = preg_replace_callback(static::$RE_MATCH, function($matches){
            $slash = $matches[1] ? '/' : '';
            $last = count($matches)=== 4 ? $matches[3] : null;
            $this->_keys[] = $matches[2];
            return $last ? (
                $last === '?' ? "(?:{$slash}([^/]+))?"     //OPTIONAL
                              : "(?:{$slash}.+?))?"        //OPTIONAL_TO_END
                            ) : "{$slash}([^/]+)";         //REQUIRED
    	}, $path);
    	$r = preg_replace(static::$RE_REPLACE, '\\/', $r);
        
        $char = $path[strlen($path) -1];
        if ($char !== '/' && $char !== '*'){ // in case no / at end
            // '/a/:a?/xyz' => \/a(?:\/([^\/]+)?)?\/xyz(?:\/)? to match '/a/xyz/' and '/a/xyz'
            $r .= '(?:\\/)?';
        }
        $r = '/^' . $r . '$/';
        if ($caseInsensitive)
            $r .= 'i';
    	return $r;
    }
    
    public function matchUrl($url) {
        if(preg_match($this->_regexp, $url, $out)){
            $r = array();
            array_shift($out);
            foreach($out as $k => $v){
                $r[$this->_keys[$k]] = $v;
            }
            return $r;
        }
    }
    
    public function makeUrl($args) {
        $associative = false;
        if (!is_array($args)){
            $args = func_get_args();
        } else {
            foreach ($args as $a => $b) {
                if (!is_int($a)) {
                    $associative = true;
                }
            }
        }
        $err = array();
        $str = preg_replace_callback(static::$RE_MATCH, function($matches) use (&$args, &$err, &$associative){
            $slash = $matches[1] ? '/' : '';
            $name = $matches[2];
            $last = count($matches)=== 4 ? $matches[3] : null;
            if ($associative) {
                $val = isset($args[$name]) ? $args[$name] : '';
            } else {
                $val = array_shift($args);
            }
            if (!($last || $val) ) {// REQUIRED
                $err[] = $name;
            }
            return $val ? "{$slash}{$val}" : "";
    	}, $this->_path);
        return count($err) ? $err : $str;
    }
    
    public function setMethods($methods)
    {
        if (is_string($methods)) {
            $this->_methods = $methods;
        } else if (is_array($methods))
            $this->_methods  = implode('|', $methods);
    }
    
    public function methods()
    {
        return $this->_methods;
    }
    
    public function accept($method)
    {
        return stripos($this->_methods, $method) !== FALSE;
    }
    
    public function __toString() {
        $r  = isset($this->_methods) ? strtoupper($this->_methods) : '';
        $r .= isset($this->_path)    ? $this->_path    : '/';
        $r .= '?';
        $arr = $this->_options;
        $attr = array();
        foreach ($arr as $k => $v) {
            if (is_numeric($k))
                continue;
            if (is_numeric($v) || is_bool($v) || is_string($v))
                $attr[] = "{$k}={$v}";
        }
        return $r . implode('&', $attr);
    }
    
    static public function create($path, $caseInsensitive = null) {
        $method = $query = null;
        if (is_array($path)) {
            $query = $path;
            if (array_key_exists('method', $query)){
                $method = $query['method'];
                unset($query['method']);
            }
            if (array_key_exists('path', $query)){
                $path = $query['path'];
                unset($query['path']);
            }
        } else {
            if (strpos($path, ' ') !== false)
                $path = preg_replace('/\s/', '', $path);
            if (false !== ($pos = strpos($path, '/'))){
                $method = substr($path, 0, $pos);
                $path = substr($path, $pos, strlen($path));
            }
            if (false !== ($pos = strpos($path, '?'))){
                $query = substr($path, $pos+1, strlen($path));
                $path = substr($path, 0, $pos);
            }
        }
        $r = new static($path, $caseInsensitive);
        if ($method)
            $r->setMethods($method);
        if ($query)
            $r->setOptions($r->parseQuery($query));
        return $r;
    }
    
    public function parseQuery ($query){
        $query = preg_replace('/\s/', '', $query);
        $data = array();
        foreach(explode('&', $query) as $v){
            list($key, $val) = explode('=', $v);
            $data[$key] = $val;
        }
        return $data;
    }
    
    public function option($key) {
        if (array_key_exists($key, $this->_options)) {
            return  $this->_options[$key];
        }
    }
    
    public function setOption($key, $value) {
        $this->_options[$key] = $value;
    }
    
    public function setOptions($options) {
        if ($options) {
            $this->_options = array_replace($this->_options, $options);
        }
    }
    
    protected $_options = [];
    
    protected $_methods;
    protected $_regexp;
    protected $_path;
    protected $_keys = array();
    
    static protected $RE_MATCH = "/(\/)?:(\w+)([\?\*])?/";
    static protected $RE_REPLACE = "/([\/]+)/";
}
