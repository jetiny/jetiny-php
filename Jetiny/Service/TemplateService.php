<?php
namespace Jetiny\Service;

class TemplateService extends \Jetiny\Base\Template
{
    public function setup($context)
    {
        $this->setOptions($context->config->get('template'));
        $cache_tmpl = $this->option('cache_tmpl');
        $cache_path = $this->option('cache_path');
        if ($cache_tmpl && !is_dir($cache_path)) {
            if (!mkdir($cache_path , 0777, TRUE)) {
                \Jetiny\Base\Exception::throwError('err.tmpl_mkdir', 'unable to make template dir: '. $cache_path);
            }
        }
    }
}