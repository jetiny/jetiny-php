<?php
namespace Jetiny\Service;

class RouterService extends \Jetiny\Base\Router
{
    public function setup($context)
    {
        $this->_caseInsensitive = $context->config->peek('router.caseInsensitive');
        $this->createRouters($context->config->peek('routers'));
    }
}
