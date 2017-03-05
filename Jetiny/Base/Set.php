<?php
namespace Jetiny\Base;

class Set implements \ArrayAccess, \Countable, \IteratorAggregate
{
    public function __construct($arr = null) {
        $this->setData($arr);
    }
    public function set($key, $value)
    {
        $this->_datas[$key] = $value;
    }
    public function get($key, $default = NULL)
    {
        return isset($this->_datas[$key]) ? $this->_datas[$key] : $default;
    }
    public function peek($key) {
        $r = $this->get($key);
        if (!is_null($r)) {
            $this->rm($key);
        }
        return $r;
    }
    public function setData($value)
    {
        $this->_datas = $value ? $value : [];
    }
    public function data()
    {
        return $this->_datas;
    }
    public function has($key)
    {
        return array_key_exists($key, $this->_datas);
    }
    public function rm($key)
    {
        unset($this->_datas[$key]);
    }
    public function __get($key)
    {
        return $this->get($key);
    }
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
    public function __isset($key)
    {
        return $this->has($key);
    }
    public function __unset($key)
    {
        $this->rm($key);
    }
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
    public function count()
    {
        return count($this->_datas);
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->_datas);
    }
    protected $_datas;
}
