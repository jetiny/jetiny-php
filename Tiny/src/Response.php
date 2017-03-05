<?php

namespace Jetiny;
use \Jetiny\Request;

class Response
{
    public function setOptions($value) {
        $this->_options = array_replace_recursive($this->_options, $value);
    }
    public function setCookie($name, $value = '') {
        if ($value === '') { // 为空认为是过期
            $value = [ 'expires'=> - 86400000 , 'value' => ''];
        }
        if (is_array($value)) {
            $value = array_replace($this->_options['cookie'], $value);
        } else {
            $value = array_replace($this->_options['cookie'], ['value' => $value]);
        }
        $this->_cookies[$name] = $value;
    }
    public function cookie($name) {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name]['value'] : NULL;
    }
    public function rmCookie($name) {
        unset($this->_cookies[$name]);
    }
    
    public function setHeader($name, $value) {
        $this->_headers[$this->normalizeHeader($name)] = $value;
    }
    public function header($name) {
        return $this->_headers[$this->normalizeHeader($name)];
    }
    public function rmHeader($name) {
        unset($this->_headers[$this->normalizeHeader($name)]);
    }
    
    public function setStatusCode($value) {
        $this->_statusCode = $value;
    }
    public function statusCode(){
        return $this->_statusCode;
    }
    
    public function setBody($data) {
        $this->_body = $data;
    }
    
    public function body() {
        return $this->_body;
    }
    
    public function setFormat($format){
       $this->_format = $format;
    }
    
    public function formt(){
       return $this->_format;
    }
    
    public function setRequest($request) {
        $this->_request = $request;
    }
    
    public function request() {
        return $this->_request;
    }
    
    public function data($error, $data, $message)
    {
        $arr = [
            'error'   => $error,
            'data'    => $data,
            'message' => $message,
        ];
        $format = $this->_request->format;
        if (!in_array($format, ['JSON', 'JS', 'XML'])){
            $format = 'JSON';
        }
        $this->setFormat($format);
        $this->setBody($arr);
    }
    
    public function error($error = 1, $message = '') {
        $this->data($error, NULL, $message);
    }
    
    public function success($data = NULL, $message = '') {
        $this->data(0, $data, $message);
    }
    
    public function send(){
        if ($this->_statusCode) {
            http_response_code($this->_statusCode);
        }
        foreach($this->_cookies as $k => $v) {
            setcookie($k, $v['value'], $v['expires'], $v['path'], $v['domain'], $v['secure'], $v['httponly']);
        }
        foreach($this->_headers as $k => $v) {
            header($k .':' . $v);
        }
        if ($format = $this->_format) {
            $contentType = \Jetiny\Request::getMimeType(strtoupper($this->_format));
            header('Content-Type', $contentType);
        }
        $data = $this->_body;
        if ($data) {
            if (is_array($data)) {
                if ($format === 'JSON' ) {
                    $data = json_encode($data);
                }else if ($format === 'JS') {
                    $data = $this->_request->jsonp . "(" . json_encode($data) . ")";
                } else if ($format === 'XML') {
                    $data = self::xmlEncode($data);
                }
            }
            header('Content-Length', strlen($data));
            echo $data;
        }
    }
    
    protected function normalizeHeader($key) {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);
        return $key;
    }
	static function xmlEncode($data, $root="root", $encoding='utf-8') {
	    $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
	    $xml.= '<' . $root . '>';
	    $xml.= self::xmlSerial($data);
	    $xml.= '</' . $root . '>';
	    return $xml;
	}
	static function xmlSerial($data) {
	    if (is_object($data)) 
	        $data = get_object_vars($data);
	    if (is_array($data)){
	        $xml = '';
	        foreach ($data as $key => $val) {
	            is_numeric($key) && $key = "item id=\"$key\"";
	            $xml.="<$key>";
	            $xml.= ( is_array($val) || is_object($val)) ? self::xmlSerial($val) : $val;
	            list($key, ) = explode(' ', $key);
	            $xml.="</$key>";
	        }
	        return $xml;
	    }else if(!empty($data)) // integer double string 等直接返回
	        return $data;
	    return '';
	}
    protected $_cookies = [];
    protected $_headers = [];
    protected $_statusCode;
    protected $_body;
    protected $_request;
    protected $_options = [
        'cookie' => [
            'value' => '',
            'prefix' => '',
            'domain' => null,
            'path' => null,
            'expires' => null,
            'secure' => false,
            'httponly' => false
        ],
    ];
}
