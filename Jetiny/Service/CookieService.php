<?php
namespace Jetiny\Service;

class CookieService extends \Jetiny\Http\ResponseCookie
{
    public function setup($context)
    {
        if ($settings = $context->config->peek('cookie')) {
            if ($prefix = isset($settings['prefix']) ? $settings['prefix'] : NULL) {
                unset($settings['prefix']);
                $this->setPrefix($prefix);
            }
            $this->setOptions($settings);
        }
    }
}
