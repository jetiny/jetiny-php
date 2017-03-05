<?php
namespace Jetiny\Service;

use \Jetiny\Base\Exception;

class ExceptionService extends Service
{
    
    public function setup($context){
        parent::setup($context);
        Exception::setExceptionHandler($this);
    }
    
    public function notices()
    {
        return $this->_notices;
    }
    
    public function error()
    {
        return $this->_error;
    }
    
    public function handleNotice($notice) {
        $this->_notices[] = $notice;
        if ($this->context->config->get('exception.notice_as_exception')) {
            $this->handleException($notice);
        }
    }
    
    public function handleException($e) {
        if ($this->context->config->get('exception.exception_as_fatal')) {
            Exception::setLastError($e);
    		exit;
        }
    }
    
    public function terminate($lasetError) {
        $this->_error = $lasetError;
        Exception::restoreExceptionHandler();
        $this->context->terminate();
    }
    
    protected $_error;
    protected $_notices = [];
}
