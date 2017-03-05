<?php
namespace Jetiny\Http;

class TextResponse extends Response
{
    
    public function __construct($buffer)
    {
        $this->_buffer = $buffer;
    }
    
    public function __toString()
    {
        return $this->_buffer;
    }
    
    protected $_buffer;
}
