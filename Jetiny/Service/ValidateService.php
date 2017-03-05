<?php
namespace Jetiny\Service;
use Jetiny\Base\Validate;

class ValidateService extends Service
{
    
    public function route($route, $req)
    {
        if ($validate = $route->option('validate')) {
            if (is_string($validate)) { //转换为数组
                $validates = static::explode('$', $validate);
                array_shift($validates);
                $items = [];
                foreach($validates as $it){
                    $it = Validate::unserialize($it);
                    $name = key($it);
                    reset($it);
                    array_shift($it);
                    $items[$name] = $it;
                }
                $validate = $items;
                $route->setOption('validate', $validate);
            }
            $args = $req->method == 'POST' ? $req->field : $req->param;
            $vstep = ($route->option('vstep') == TRUE);
            if ($errors = Validate::checkAll($args, $validate, $vstep)) {
                foreach($errors as $k => &$v) {
                    $v = $this->format($v);
                }
                $route->error  = $errors;
            }
        }
    }
    
    public function format($err)
    {
        $text = $this->context->lang->tr('validate', $err['error']);
        $params = $err['params'];
        $text = preg_replace_callback('/{(\d+)}/', function($ma) use($params){
            return $params[$ma[1]];
        }, $text);
        return $text;
    }
    
    // php explod can not work with $ explod('$', '$username|required|len:3,2|between:4,5|lmax:1 $age|gt:18|lt:120')
    static function explode($char, $str)
    {
       $r = [];
       $p = $n = 0;
       while (FALSE !== ($p = strpos($str, $char, $n))) {
           $r[] = substr($str, $n, $p - $n);
           $n = $p + 1;
       }
       $p = strlen($str);
       $r[] = $n == $p ? '' : substr($str, $n, $p);
       return $r;
    }
}