<?php
namespace Jetiny\Cache;

class ChainCache extends CacheHandler
{
    public function setPrefix($prefix)
    {
        parent::setPrefix($prefix);
        foreach ($this->cachePool as $it) {
            $it->setPrefix($prefix);
        }
    }
    protected function doFetch($id)
    {
        foreach ($this->cachePool as $key => $it) {
            if ($it->doContains($id)) {
                $value = $it->doFetch($id);
                // We populate all the previous cache layers (that are assumed to be faster)
                for ($subKey = $key - 1 ; $subKey >= 0 ; $subKey--) {
                    $this->cachePool[$subKey]->doSave($id, $value);
                }
                return $value;
            }
        }
        return false;
    }
    protected function doContains($id)
    {
        foreach ($this->cachePool as $it) {
            if ($it->doContains($id)) {
                return true;
            }
        }
        return false;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $r = true;
        foreach ($this->cachePool as $it) {
            $r = $it->doSave($id, $data, $lifeTime) && $r;
        }
        return $r;
    }
    protected function doDelete($id)
    {
        $r = true;
        foreach ($this->cachePool as $it) {
            $r = $it->doDelete($id) && $r;
        }
        return $r;
    }
    protected function doFlush()
    {
        $r = true;
        foreach ($this->cachePool as $it) {
            $r = $it->doFlush() && $r;
        }
        return $r;
    }
    public function setCachePool($cachePool)
    {
        $this->cachePool = $cachePool;
    }
    private $cachePool = [];
}
