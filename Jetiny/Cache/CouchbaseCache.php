<?php
namespace Jetiny\Cache;

class CouchbaseCache extends CacheHandler
{
    // windows 下安装不成功,后面在linux下再测试吧
    protected function doFetch($id)
    {
        return $this->handler->get($id) ?: false;
    }
    protected function doContains($id)
    {
        return (null !== $this->handler->get($id));
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->handler->set($id, $data, (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return $this->handler->delete($id);
    }
    protected function doFlush()
    {
        return $this->handler->flush();
    }
}
