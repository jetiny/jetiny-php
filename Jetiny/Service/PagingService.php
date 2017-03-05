<?php
namespace Jetiny\Service;

class PagingService extends \Jetiny\Base\Paging
{
    
    public function setup($context)
    {
        $this->setOptions($context->config->peek('paging'));
        $this->app =$context;
    }
    
    public function parse($arr = NULL)
    {
        if (!$arr) {
            $arr = $this->app->request->param;
        }
        return parent::parse($arr);
    }
    
}