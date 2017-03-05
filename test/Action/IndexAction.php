<?php
namespace App\Action;

class IndexAction extends \Jetiny\Action
{
    
    public function index()
    {
        return $this->display('test');
        //return $this->location('http://www.baidu.com');
        //return $this->text('Hello');
        //return $this->success();
    }
    
    const action_const = 'format=html';
    public function action_const()
    {
        return $this->text('action_const');
    }
    
    public static $action_static = 'format=html';
    public function action_static()
    {
        return $this->text('action_static');
    }
    
    public static $action_public = array(
        'format' => 'html',
    );
    public function action_public()
    {
        return $this->text('action_public');
    }
    
    const jsonFormat = 'format=json';
    public function jsonFormat()
    {
        return $this->success()->json();
    }
    
    const jsonpFormat = 'format=jsonp';
    public function jsonpFormat()
    {
        return $this->success()->jsonp();
    }
    
    public static $validate = array(
        'validate' => [
            'username' => [
                'required',
                'len'=>[2,6],
            ]
        ],
        'vstep'   => TRUE, //校验遇到错误就停止,还是执行全部校验, 默认FALSE
    );
    
    //const validate = 'validate=$username|required|len:3,2|between:4,5|lmax:1 $age|gt:18|lt:120';
    public function validate()
    {
        var_dump($this->route->error, $this->route->errors);
    }
    
    const validateError = 'vstep=TRUE&validate=$username|required|len:3,2';
    public function validateError()
    {
        var_dump($this->route->error);
    }
    
    const validateErrors = 'validate=$username|len:3,2 $age|between:5,4';
    public function validateErrors()
    {
        var_dump($this->route->error);
    }
    
    public function tr()
    {
        var_dump($this->lang->tr('err.invalid_phone'));
    }
    
    public function xcache()
    {
        
        $cache = $this->cache;
        $cache->inc('incx', -100);
        $cache->dec('decx', 100);
        var_dump($cache->get('incx'), $cache->get('decx'));
        $cache->set('mmb','ttx');
        var_dump($cache->get('mmb'),$cache->has('mmb'),$cache->has('bmmb'), $cache->get('bmmb'));
        $cache->delete('mmb');
        var_dump($cache->has('mmb'));
        
        $cache->set('bmmb', [1,2,3], 10);
        var_dump($cache->get('bmmb'));
        
        //$cache->upgrade();
    }
}