<?php
namespace Jetiny\Base;

class Exception extends \Exception
{
    public function __construct ($message = '', $error = 'err.exception'){
        parent::__construct($message, 1);
        $this->code = $error;
    }
    static function throwMessage($message = ''){
        throw new static($message);
    }
    static function throwError($error, $message = ''){
        throw new static($message, $error);
    }
    static function assert($expression, $error, $message = ''){
        if(!assert($expression)){
            throw new static($message, $error);
        }
    }
    static function catchExp($e){
        if (is_a($e,'\Exception')) {
            $code = $exp['code'] = $e->getCode();
    		$exp['message']  = $e->getMessage();
    		if(is_a($e,'Jetiny\Base\Exception') && count($t = $e->getTrace()) && $t = &$t[0]){
    			$exp['file']	 = $t['file'];
    			$exp['line']	 = $t['line'];
    		}else{
    			$exp['file']	 = $e->getFile();
    			$exp['line']	 = $e->getLine();
    		}
            $e = $exp;
        }
        if (is_array($e)) {
            $e['error'] = isset($e['code']) && is_string($e['code']) ? $e['code'] : 'err.exception';
        }
        return $e;
    }
    static public $lastError = null;
    static function setLastError($err){
        self::$lastError = static::catchExp($err);
    }
    static function registerHandle($intf){
        //system error code
		//$_errors  = array(1,4,16,64,256);
		$_notices = array(2,8,32,128,512,1024,2048,8192,16384);
        
		//set_exception_handler Exception
		$exception_handler = function($e){
			Exception::setLastError($e);
			exit;
		};
        set_exception_handler($exception_handler); // Throwable
        
		//register_shutdown_function FatalError
		$_shutdown = function () use (&$_notices, &$_errors, &$intf){
		    if ($e = error_get_last()){
		        $e['code'] = $e['type']; unset($e['type']);
                if (!in_array($e['code'], $_notices)){
                    Exception::setLastError($e);
                }
                else if ($intf) {
                    $intf->_notice($e);
                }
		    }
            if ($intf) {
                $intf->_flush(Exception::$lastError);
            }
			if (function_exists('fastcgi_finish_request')){
                fastcgi_finish_request();
            }
            if ($intf) {
                $intf->_shutdown();
            }
		    exit; //force shutdown
		};
		register_shutdown_function($_shutdown);
        
        //set_error_handler
		$error_handler = function ($c, $m, $f, $l) use(&$_notices, &$exception_handler, &$intf){
		    $e = array('error'=>$c, 'message'=>$m, 'file'=>$f, 'line'=> $l);
            if (!in_array($e['error'], $_notices))
			    $exception_handler($e);
            else if ($intf)
                $intf->_notice($e);
		};
		set_error_handler($error_handler);
    }
    
    static function setExceptionHandler($intf){
        //system error code
		//$_errors  = array(1,4,16,64,256);
		$_notices = array(2,8,32,128,512,1024,2048,8192,16384);
        
		//set_exception_handler Exception
        set_exception_handler(array($intf, 'handleException')); // Throwable
        
		//register_shutdown_function FatalError
		register_shutdown_function(function () use (&$_notices, &$_errors, &$intf){
		    if ($e = error_get_last()){
		        $e['code'] = $e['type']; unset($e['type']);
                if (!in_array($e['code'], $_notices))
                    Exception::setLastError($e);
                else
                    $intf->handleNotice($e);
		    }
            $intf->terminate(Exception::$lastError);
		    exit; //force shutdown
		});
        
        //set_error_handler
		set_error_handler(function ($c, $m, $f, $l) use(&$_notices, &$intf){
		    $e = array('error'=>$c, 'message'=>$m, 'file'=>$f, 'line'=> $l);
            if (!in_array($e['error'], $_notices))
			    $intf->handleException($e);
            else
                $intf->handleNotice($e);
		});
    }
    
    static function restoreExceptionHandler()
    {
        restore_error_handler ();
        restore_exception_handler();
    }
}
