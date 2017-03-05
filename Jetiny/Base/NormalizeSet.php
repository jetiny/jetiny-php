<?php
namespace Jetiny\Base;

class NormalizeSet extends Set
{
    public function get($key, $prefix = null) {
        $key = $this->normalize($key, $prefix);
        return isset($this->_datas[$key]) ? $this->_datas[$key] : null;
    }
    
    public function set($key, $value, $prefix = null)
    {
        $this->_datas[$this->normalize($key, $prefix)] = $value;
    }
    
    public function rm($key, $prefix = null)
    {
        unset($this->_datas[$this->normalize($key, $prefix)]);
    }
    
    public function peek($key, $prefix = null) {
        $r = $this->get($key, $prefix);
        if (!is_null($r)) {
            $this->rm($key, $prefix);
        }
        return $r;
    }
    
    public function prefix() {
        return $this->_prefix;
    }
    
    public function setPrefix($value) {
        $this->_prefix = $value ? $value : '';
    }
    
    protected function normalize($key, $prefix) {
        return is_null($prefix) ? $this->_prefix.$key : $prefix.$key;
    }
    
    protected $_prefix = '';
}
