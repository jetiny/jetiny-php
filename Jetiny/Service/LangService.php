<?php
namespace Jetiny\Service;

class LangService extends \Jetiny\Base\Lang
{
    
    public function setup($context)
    {
        $langPath = __DIR__ .'/../Lang/';
        $this->setLocal($context->config->get('lang.local', 'zh-CN'));
        if ($path = $context->config->get('lang.path')) {
            $this->_paths = [$path, $langPath];
        } else if ($paths = $context->config->get('lang.paths')) {
            $this->_paths = array_merge($paths, [ $langPath ]);
        } else {
            $this->_paths[] = $langPath;
        }
        $this->preload('default');
    }
    
}