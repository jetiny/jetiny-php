<?php
namespace Jetiny\Cache;

abstract class CacheHandler implements CacheInterface
{
    public function setup($options)
    {
        if (isset($options['prefix']))
            $this->setPrefix($options['prefix']);
    }
    public function inc($id, $offset = 1)
    {
        if ($this->has($id)) {
            return $this->set($id, (int)$this->get($id) + $offset);
        } else {
            return $this->set($id, $offset);
        }
    }
    public function dec($id, $offset = 1)
    {
        return $this->inc($id, - $offset);
    }
    public function get($id)
    {
        return $this->doFetch($this->normalizeKey($id));
    }
    public function mget($ids)
    {
        $keyMaps = array_combine($ids, array_map([$this, 'normalizeKey'], $ids));
        $items = $this->doMget($keyMaps);
        $r  = [];
        foreach ($keyMaps as $key => $prefixKey) {
            $r[$key] = $items[$prefixKey];
        }
        return $r;
    }
    public function has($id)
    {
        return $this->doContains($this->normalizeKey($id));
    }
    public function set($id, $data, $lifeTime = 0)
    {
        return $this->doSave($this->normalizeKey($id), $data, $lifeTime);
    }
    public function delete($id)
    {
        return $this->doDelete($this->normalizeKey($id));
    }
    public function flush()
    {
        return $this->doFlush();
    }
    public function upgrade()
    {
        return $this->doSave($this->prefixKey(), $this->_version = $this->version() + 1);
    }
    protected function normalizeKey($id)
    {
        return sprintf('%s[%s][%s]', $this->_prefix, $this->version(), $id);
    }
    private function prefixKey()
    {
        return sprintf('%s[%s]', __CLASS__, $this->_prefix);
    }
    private function version()
    {
        if (null !== $this->_version) {
            return $this->_version;
        }
        $prefixKey = $this->prefixKey();
        $_version = $this->doFetch($prefixKey);
        if (false === $_version) {
            $_version = 1;
            $this->doSave($prefixKey, $_version);
        }
        return $this->_version = $_version;
    }
    protected function doMget($keys)
    {
        $r = [];
        foreach ($keys as $index => $key) {
            $r[$key] = $this->doFetch($key);
        }
        return $r;
    }
    public function setPrefix($prefix)
    {
        $this->_prefix        = $prefix;
        $this->_version = null;
    }
    public function setHandler($handler) {
        $this->handler = $handler;
    }
    public function getHandler() {
        return $this->handler;
    }
    private $_prefix = '';
    private $_version;
    protected $handler;
    abstract protected function doFetch($id);
    abstract protected function doContains($id);
    abstract protected function doSave($id, $data, $lifeTime = 0);
    abstract protected function doDelete($id);
    abstract protected function doFlush();
}
