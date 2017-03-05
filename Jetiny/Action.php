<?php
namespace Jetiny;

use Jetiny\Http\HtmlResponse;
use Jetiny\Http\DataResponse;
use Jetiny\Http\LocationResponse;

class Action
{
    
    public function setup($app)
    {
        $this->app = $app;
    }
    
    public function __get($key) {
        return $this->app->{$key};
    }
    
    // template
    public function assign($key, $value)
    {
        $this->template->assign($key, $value);
    }
    
    // template response
    public function display($tpl)
    {
        return (new HtmlResponse($this->template))->setup($this->app)->tmpl($tpl);
    }
    
    // text response
    public function text($buffer)
    {
        return (new TextResponse($buffer))->setup($this->app);
    }
    
    // data response
    public function success($data = NULL, $message = NULL)
    {
        return (new DataResponse(0, $data, $message))->setup($this->app);
    }
    
    public function error($message = NULL, $data = NULL, $error = 1)
    {
        return (new DataResponse($error, $message, $data))->setup($this->app);
    }
    
    // json
/*    function jsonData($data = null, $message = null) {
        return JsonResponse::success(0, $data, $message);
    }
    function jsonMessage($message, $data = null) {
        return JsonResponse::successMessage(0, $data, $message);
    }
    function jsonError($error = -1, $data = null,  $message = null) {
        return JsonResponse::error($error, $data, $message);
    }
    function jsonErrorMessage($message, $error = -1) {
        return JsonResponse::errorMessage($error, null, $message);
    }
    function jsonErrorData($data, $error = -1) {
        return JsonResponse::errorData($error, null, $data);
    }*/
    
    // location
    public function location($url, $status = 302) {
        return new LocationResponse($url, $status);
    }
    
}
