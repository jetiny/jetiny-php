<?php
namespace Jetiny\Base;

class Util
{
    static function slashUrl($url) {// 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF'
        return preg_replace('/\/+/', '/', $url);
    }
    static function isSerialized( $data ) {
    	 if ( 'N;' == $data ){
    	 	return true;
    	 }elseif (preg_match( '/^([adObis]):/', $data, $r ) ){
    		 switch ( $r[1] ) {
    			 case 'a' :case 'O' :case 's' :
    				 if ( preg_match( "/^{$r[1]}:[0-9]+:.*[;}]\$/s", $data ) )
    					 return true;
    				 break;
    			 case 'b' :case 'i' :case 'd' :
    				 if ( preg_match( "/^{$r[1]}:[0-9.E-]+;\$/", $data ) )
    					 return true;
    				 break;
    		 }
    	 }
    	 return false;
    }
    static function arrayExtract(&$arr, $key)
    {
        if (is_array($arr) && array_key_exists($key, $arr)) {
            $r = $arr[$key];
            unset($arr[$key]);
            return $r;
        }
    }
    
    static function explodeShift($key, $dot = '.')
    {
        $keys = explode($dot, $key);
        return array(array_shift($keys), $keys);
    }
    
    static function uuid() {
    	if (function_exists('com_create_guid')){
            return strtolower(str_replace(array('{','}','-'),'',com_create_guid()));
        }
        mt_srand((double)microtime()*10000);
        $charid = strtolower(md5(uniqid(rand(), true)));
        $uuid =  substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4)
                .substr($charid,16, 4).substr($charid,20,12);
        return $uuid;
    }
    
    static function & extend ( &$r, $key, $val) {
        if (is_array($key)) {
            $r = array_replace_recursive($r, $key);
        } else {
            $r = & $r[$key];
            $r = $val;
        }
        return $r;
    }
    
    protected static $_formats;
    protected static function initializeFormats() {
        static::$_formats = array(
            'HTML' => array('text/html', 'application/xhtml+xml'),
            'JSON' => array('application/json', 'application/x-json', 'text/json'),
            'XML'  => array('text/xml', 'application/xml', 'application/x-xml'),
            'JSONP'=> array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'TXT'  => array('text/plain'),
            'FORM' => array('application/x-www-form-urlencoded'),
        );
    }
    
    static function getFormat($mimeType) {
        if (false !== $pos = strpos($mimeType, ';')) {
            $mimeType = substr($mimeType, 0, $pos);
        }
        if (false !== $pos = strpos($mimeType, ',')) {
            $mimeType = substr($mimeType, 0, $pos);
        }
        if (null === static::$_formats) {
            static::initializeFormats();
        }
        foreach (static::$_formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
    }
    
    static function getMimeType($format){
        if (null === static::$_formats) {
            static::initializeFormats();
        }
        return isset(static::$_formats[$format]) ? static::$_formats[$format][0] : null;
    }
    
    // Directory
    // path & directory converter
    static function pathDir($url, $toPath = true){
    	$url = preg_replace('/\\\/', '/', $url);
    	if($toPath !== (substr($url, -1) === '/'))
    	   return $toPath ? $url.'/' : substr($url,0,strlen($url)-1);
    	return $url;
    }
    // delete files in path
    static function deleteFiles($path, $ext = '', $level = 0 ){
    	if($hand = opendir($path)){
    		$l = strlen($ext);
    		while(($file = readdir($hand))!==false){
    			if($file=='.'||$file=='..')
    				continue;
    			if(is_dir($d = $path.'/'.$file)){
    				static::removePath($d, $ext, $level+1);
    			}else if(!$l || (substr($file, -$l) == $ext)){
    				@unlink($d);
    			}
    		}
    		closedir($hand);
    		if($level)
    			@rmdir($path);
    	}
    }
    
    // auto load class
    static function classExists($class) {
        if (class_exists($class))
            return TRUE;
        return FALSE !== @class_implements($class, true);
    }
    
    /**
    * 生成分页区间
    * @param integer $total    总页数
    * @param integer $curr     当前页
    * @param integer $step     间距
    * 
    * @return array | null    参考 pageExtend
    */
    function pageRange ($total, $curr, $step) {
        if (!($curr > 0)){ //纠正为第一页
            $curr = 1;
        }
        //全部非负数,且当前页小于等于总页码
        if (min($step, $total, $curr, $total - $curr +1) > 0) {
            $start = intval(floor(($curr -1)/$step)) * $step; //起始位置前移一位
            $end   = min($start + $step, $total);    //结束位置
            $start = $start + 1;    //修正起始位置
            return [$curr, $start, $end];
        }
    }
    /**
    * 分页区间扩展 (不做参数校验)
    * @param integer $curr     当前页码
    * @param integer $start    区间起始位置
    * @param integer $end      区间结束位置
    * @param integer $total    总页数
    * 
    * @return array
    */
    function pageExtend($curr, $start, $end, $total) {
        return [
            "first" => 1,        //首页序号
            "last"  => $total,   //尾页序号
            
            "prev"  => ($curr > 1) ? $curr - 1 : 0 ,      //上一页序号
            "next"  => ($curr != $total) ? $curr +1 : 0,  //下一页序号
            
            "pprev"  => ($start > 1) ? $start - 1 : 0 ,   //上一组最后一个序号
            "nnext"  => ($end < $total) ? $end +1 : 0,    //下一组第一个序号
        ];
    }
}
