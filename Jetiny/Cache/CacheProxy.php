<?php
namespace Jetiny\Cache;

class CacheProxy implements CacheInterface
{
    public function mget($ids)
    {
        return $this->cacher->mget($ids);
    }
    public function get($id)
    {
        return $this->cacher->get($id);
    }
    public function has($id)
    {
        return $this->cacher->has($id);
    }
    public function set($id, $data, $lifeTime = 0)
    {
        return $this->cacher->set($id, $data, $lifeTime);
    }
    public function delete($id)
    {
        return $this->cacher->delete($id);
    }
    public function inc($id, $offset = 1)
    {
        return $this->cacher->inc($id, $offset);
    }
    public function dec($id, $offset = -1)
    {
        return $this->cacher->dec($id, $offset);
    }
    public function flush()
    {
        return $this->cacher->flush();
    }
    public function upgrade()
    {
        return $this->cacher->upgrade();
    }
    public function setCacher($cacher) {
        $this->cacher = $cacher;
    }
    protected $cacher;
}