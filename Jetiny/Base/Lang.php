<?php
namespace Jetiny\Base;
use Jetiny\Base\Config;

class Lang
{
    
    public function tr($module, $key = NULL) {
        $key = is_null($key) ? $module : $module.'.' . $key;
        
        $r = $this->_locals[$this->_local];
        
        if (isset($r[$key])){ // full key path module.mmx
            return $r[$key];
        }
        list ($module, $keys) = static::explode_module($key);
        if (!isset($r[$module])) { // load
            $this->preload($module);
        }
        if (isset($r[$key])){ // full key path module.mmx
            return $r[$key];
        }
        
        for(;;){
            if (array_key_exists($module, $r)) {
                $r = $r[$module];
                $module = array_shift($keys);
                if (!$module)
                    break;
            } else {
                $r = NULL;
                break;
            }
        }
        
        return is_null($r) ? "[$key]" : $r;
    }
    
    public function setLocal($local)
    {
        $this->_local = $local;
        if (!isset($this->_locals[$local])) {
            $this->_locals[$local] = [];
        }
    }
    
    protected function preload($module) { // common
        $local = $this->_local;
        $suffix = $this->_suffix;
        foreach($this->_paths as $it) {
            $it = $it . "$module.$local.$suffix";
            if (is_readable($it)) {
                if ($data = $this->load($it)) {
                    $this->extend($data);
                }
            }
        }
    }
    
    protected function extend($key, $value = null)
    {
        $r = & $this->_locals[$this->_local];
        if (is_string($key)) {
            list ($module, $keys) = static::explode_module($key);
            while($module) {
                $r = & $r[$module];
                $module = array_shift($keys);
            }
        }
        else {
            $value = $key;
        }
        $r = is_array($value) ? (is_array($r) ? array_replace_recursive($r, $value) : $value ) : $value;
    }
    
    static protected function explode_module ($key, $dot = '.')
    {
        $keys = explode($dot, $key);
        return array(array_shift($keys), $keys);
    }
    
    protected function load($path)
    {
        if ($this->_suffix == 'php')
            return include $path;
        else if ($this->_suffix == 'json')
            return json_decode(file_get_contents($path));
    }
    
    protected $_suffix = 'php';
    protected $_paths = [];
    protected $_local = 'en';
    protected $_locals = [];
}
