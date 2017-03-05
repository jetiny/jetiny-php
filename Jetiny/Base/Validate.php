<?php
namespace Jetiny\Base;
use Jetiny\Base\Exception;

class Validate
{
	const IS_EMPTY    = 'empty';
	const IS_INVALID  = 'invalid';
//-----------------------------注册校验方法
	public static function register($name, $method) {
        if (is_callable($method)) {
		    static::$_methods[$name] = $method ;
        }
        else {
		    static::$_regexps[$name] = $method ;
        }
	}
//-----------------------------执行校验
    public static function check(&$arg, $arr) {
        $errs = [];
        if (is_string($arr)) {
            $arr = static::unserialize($arr);
        }
        foreach ($arr as $method => $opts) {
            if (is_numeric($method)) {
                $method = $opts;
                $opts = null;
            }
            if (is_callable($opts)) { //自定义函数
                $fn = $opts;
                $opts = null;
            } else if (isset(static::$_methods[$method])) { //注册方法
                $fn = static::$_methods[$method];
            } else if (is_callable('static::'.$method)) { //静态方法
                $fn = ['static', $method];
            } else if (isset(static::$_regexps[$method])) { //注册正则
                $fn = ['static', 'regex'];
                $opts = $method;
            } else {
                Exception::throwError('err.validate_no_found', 'Validate not found :' . $method);
            }
            if ($r = call_user_func_array($fn, [&$arg, $opts])) {
                $err = $r;
                if (is_string($err)) {
                    $err = ['error' => $r];
                }
                return $err;
            }
        }
    }
    
    public static function checkAll(&$args, $arr, $break = TRUE) {
        $errs = [];
        foreach ($arr as $name => $fields) {
            if (isset($args[$name])) {
                $value = & $args[$name];
                $err = static::check($value, $fields);
            } else {
                $err = ['error' => static::IS_EMPTY];
            }
            if ($err) {
                if ($break) {
                    return [$name => $err];
                } else {
                    $errs[$name] = $err;
                }
            }
        }
        if (count($errs)) return $errs;
    }
    // 反序列化
    public static function unserialize($arr) {// 'required|len:3,2|between:4,5|lmin:2'
        $dist = [];
        foreach(explode('|', trim($arr)) as $k => $v) {
            $it = explode(':', $v);
            $k = $it[0];
            if (count($it) > 1) {
                $v = explode(',', $it[1]);
            }
            $dist[$k] = is_array($v) ? ( count($v) == 1 ? $v[0] : $v ) : TRUE;
        }
        return $dist;
    }
//-----------------------------类型转换
	public static function required(&$val) {
		if(empty($val) || (is_string($val) && empty($val = trim($val))) ){
			return __FUNCTION__;
		}
	}
    public static function numeric(&$val) {
        if (!is_numeric($val)) return __FUNCTION__;
        $val += 0;
    }
//-----------------------------字符串
    public static function lmin(&$val, $len) {
        if ($r = static::required($val)) return $r;
        if (strlen($val) < $len)    return static::errorParams(__FUNCTION__, $len);
    }
    public static function lmax(&$val, $len) {
        if ($r = static::required($val)) return $r;
        if (strlen($val) > $len)    return static::errorParams(__FUNCTION__, $len);
    }
    public static function len(&$val, $lens) {
        if ($r = static::required($val)) return $r;
        $l = strlen($val);
        if ($l < $lens[0] || $l > $lens[1] )  return static::errorParams(__FUNCTION__, $lens);
    }
//-----------------------------数字
    //in
    public static function in(&$val, $arr) {
        if (!in_array($val, $arr)) return static::errorParams(__FUNCTION__, $arr);
    }
    //between
    public static function between(&$val, $arr) {
        if ($r = static::numeric($val)) return $r;
        if ($val < $arr[0] || $val > $arr[1]) return static::errorParams(__FUNCTION__, $arr);
    }
    // ==
    public static function eq(&$val, $tar) {
        if ($val != $tar) return __FUNCTION__;
    }
    // !=
    public static function neq(&$val, $tar) {
        if ($val == $tar) return __FUNCTION__;
    }
    // >
    public static function gt(&$val, $tar) {
        if ($r = static::numeric($val)) return $r;
        if (!($val > $tar)) return static::errorParams(__FUNCTION__, $tar);
    }
    // >=
    public static function egt(&$val, $tar) {
        if ($r = static::numeric($val)) return $r;
        if ($val < $tar) return static::errorParams(__FUNCTION__, $tar);
    }
    // <
    public static function lt(&$val, $tar) {
        if ($r = static::numeric($val)) return $r;
        if (!($val < $tar)) return static::errorParams(__FUNCTION__, $tar);
    }
    // <=
    public static function elt(&$val, $tar) {
        if ($r = static::numeric($val)) return $r;
        if ($val > $tar) return static::errorParams(__FUNCTION__, $tar);
    }
    
    public static function errorParams($error, $params) {
        return [
            'error'  => $error,
            'params'  => is_array($params) ? $params : [$params]
        ];
    }
//-----------------------------正则
	public static function regex(&$val, $method){
        if ($r = static::required($val)) {
            return $r;
        }
        $pattern = isset(static::$_regexps[$method]) ? static::$_regexps[$method] : $method;
		if (!preg_match($pattern, $val))
			return $pattern != $method ? $method : static::IS_INVALID;
	}
	static protected $_regexps = [
        'char' => '/^[A-Za-z]+$/',                  //英文字母
        'code' => '/^\[A-Za-z0-9]+$/',              //字母和数字
        'url' => "/^http(s):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i", //超链接
        'email'=>'/^[_\.0-9a-z-a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i',                    // 邮箱
        'phone'=>'/^((86)[\+-]?)?^1\d{10}$/i',     //手机
        'zip'=>'/^[1-9]\d{5}$/',                    //邮编
        'qq'=>'/^\d{5,13}$/i',                      //QQ号
        'number' => '/^\d+$/',                      //正整数,不限位数
        'float' => '/^\d+(\.\d+)?$/',               //正浮点数,不限位数
        'date'=>'/^\d{4}-?((0[1-9])|(1[0-2])|[1-9])-?((0[1-9])|([1-2][0-9])|(3[0-1])|[1-9])$/i',//日期
        'time'=>'/^(([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)$/',                           //时间
        'ipv4'=>'/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',
	];
//------------------------------自定义方法
	static protected $_methods = [];
}

/*if (1) {
    $r = ' ';
    var_dump(Validate::check($r, 'required'));
    
    $r = ['name'=> 'jetiny'];
    var_dump(Validate::checkAll($r, ['name' => 'len:1,5']));
    
    if (0) {
        $r = ' 1 ';
        var_dump(Validate::check($r, [
            'required' => true ,
            'char' => true,
            'mmx' => function(){
                return 'bbx';
            },
            'gt' => 100,
            'lt' => 0,
            'eq' => 1,
            'neq' => 1,
            'between' => [2,3],
            'in' => [0,2,3]
        ], FALSE));
        var_dump($r);
    }
    if (0) {
        $arr = [
            'user_name' => '`~@#$%xx',
            'phone' => '12346*',
            'user_age' => 'hxx',
        ];
        var_dump(Validate::checkAll($arr, [
            'phone'   => ['phone' => TRUE],
            'user_age' => ['between' => [18, 120]],
            'user_name' => 'required|len:3,2|between:4,5|lmax:1',
            'nothing' => 'required',
        ], FALSE));
    }
}*/
