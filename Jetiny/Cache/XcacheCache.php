<?php
namespace Jetiny\Cache;
use Jetiny\Base\Util;

class XcacheCache extends CacheHandler
{
    
    public function setup($options)
    {
        if (ini_get('xcache.admin.enable_auth')) {
            throw new \BadMethodCallException(
                'you must set "xcache.admin.enable_auth = Off" in your php.ini.');
        }
    }
    protected function doFetch($id)
    {
        return $this->doContains($id) ? $this->unserialize(xcache_get($id)) : false;
    }
    protected function doContains($id)
    {
        return xcache_isset($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return xcache_set($id, $this->serialize($data), (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return xcache_unset($id);
    }
    protected function doFlush() { // set "xcache.admin.enable_auth = Off" to " in php.ini
        xcache_clear_cache(XC_TYPE_VAR);
        return true;
    }
    public function inc($id, $offset = 1)
    {
        return xcache_inc($this->normalizeKey($id), $offset);
    }
    public function dec($id, $offset = 1)
    {
        return xcache_dec($this->normalizeKey($id), $offset);
    }
    
    protected function serialize($data)
    {
        return (is_array($data) || is_object($data)) ? serialize($data) : $data;
    }
    
    protected function unserialize($data)
    {
        if (is_string($data)) {
            if (Util::isSerialized($data))
                return unserialize($data);
        }
        return $data;
    }
    
}
