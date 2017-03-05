<?php
namespace Jetiny\Http;

class HtmlResponse extends Response
{
    
    public function __construct($engine = NULL)
    {
        $this->engine = $engine;
    }
    
    public function tmpl($tpl)
    {
        $level = ob_get_level();
        ob_start();
        try {
            $this->engine->display($tpl);
        } catch (\Exception $e) {
            \Jetiny\Base\Exception::setLastError($e);
        } finally {
            $bufs = array();
            while(ob_get_level() > $level) {
                $bufs[] = ob_get_clean();
            }
            $this->_buffer = implode(array_reverse($bufs));
        }
        return $this;
    }
    
    public function text($data)
    {
        $this->_buffer = $data;
        return $this;
    }
    
    public function __toString()
    {
        return $this->_buffer;
    }
    
    protected $_buffer;
}
