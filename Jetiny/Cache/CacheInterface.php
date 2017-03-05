<?php
namespace Jetiny\Cache;

interface CacheInterface
{
    // basic
    public function get($id);
    public function has($id);
    public function set($id, $data, $lifeTime = 0);
    public function delete($id);
    
    // extend
    public function inc($id, $offset = 1);   // lifeTime = 0
    public function dec($id, $offset = 1);   // lifeTime = 0
    public function mget($ids);
    public function flush();
    public function upgrade();
}
