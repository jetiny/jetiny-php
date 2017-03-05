<?php
namespace Jetiny\Base;

class Config
{
    function get($key, $default = null)
    {
        $r = $this->_datas;
        if ($key) {
            list ($module, $keys) = static::explode_module($key);
            for(;;){
                if (array_key_exists($module, $r)) {
                    $r = $r[$module];
                    $module = array_shift($keys);
                    if (!$module)
                        break;
                }
                else {
                    return is_callable($default) ? $default() : $default;
                }
            }
        }
        return $r;
    }
    
    function set($key, $value)
    {
        list ($module, $keys) = static::explode_module($key);
        $r = & $this->_datas;
        while($module) {
            $r = & $r[$module];
            $module = array_shift($keys);
        }
        $r = $value;
    }
    
    function peek($key, $default = null) {
        $r = $this->get($key, $default);
        $this->set($key, NULL);
        return $r;
    }
    
    function extend($key, $value = null)
    {
        $r = & $this->_datas;
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
    
    function remove($key)
    {
        list ($module, $keys) = static::explode_module($key);
        $r = & $this->_datas;
        for(;;){
            if (array_key_exists($module, $r)) {
                $next = array_shift($keys);
                if (!$next){
                    unset($r[$module]);
                    return ;
                }
                $r =  & $r[$module];
                $module = $next;
            }
            else return;
        }
    }
    
    static protected function explode_module ($key, $dot = '.')
    {
        $keys = explode($dot, $key);
        return array(array_shift($keys), $keys);
    }
    
    protected $_datas = array();
}