<?php
namespace Jetiny\Http;

class JsonResponse extends Response
{
    public function __construct($error, $data,  $message) {
        $this->error = $error;
        $this->message = $message;
        $this->data = $data;
    }
    static function success($data = null, $message = null) {
        return new static(0, $data, $message);
    }
    static function successMessage($message, $data = null) {
        return new static(0, $data, $message);
    }
    static function error($error = -1, $data = null,  $message = null) {
        return new static($error, $data, $message);
    }
    static function errorMessage($message, $error = -1) {
        return new static($error, null, $message);
    }
    static function errorData($data, $error = -1) {
        return new static($error, null, $data);
    }
    
    public function contentType()
    {
        return \Jetiny\Base\Util::getMimeType('JSON');
    }
    
    public function __toString()
    {
        return json_encode($this);
    }
}