<?php
namespace Jetiny\Http;

use Jetiny\Base\Set;
use Jetiny\Base\NormalizeSet;
use Jetiny\Base\Util;
use Jetiny\Http\RequestHeader;

class Request
{
    
    public function __get($key) {
        if (method_exists($this, $m = '_get' . ucwords($key))) {
            return $this->{$key} = $this->{$m}();
        }
    }
    
    protected function _getQuery()
    {
        $r = new Set();
        $r->setData($_GET);
        return $r;
    }
    
    protected function _getField()
    {
        $r = new Set();
        $r->setData($_POST);
        return $r;
    }
    
    protected function _getParam()
    {
        $r = new Set();
        $r->setData($_REQUEST);
        return $r;
    }
    
    protected function _getCookie()
    {
        $r = new NormalizeSet();
        $r->setPrefix($this->option('cookie_prefix'));
        $r->setData($_COOKIE);
        return $r;
    }
    
    protected function _getHeader()
    {
        $r = new RequestHeader();
        $r->setData(RequestHeader::extract($_SERVER));
        return $r;
    }
    
    // utils
    public function _getMethod()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        if ($method=== 'POST' && isset($_SERVER['X-HTTP-METHOD-OVERRIDE']))
            $method = strtoupper($_SERVER['X-HTTP-METHOD-OVERRIDE']);
        return strtoupper($method);
    }
    
    public function _getFormat()
    {
        if ($format = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null ) {
            $format = Util::getFormat($format);
        }
        return $format ? $format : 'HTML';
    }
    
    public function _getAjax()
    {
        return isset($_SERVER['X-Requested-With']) && ($_SERVER['X-Requested-With'] == 'XMLHttpRequest');
    }
    
    // Url
    public function _getHost(){
        return $_SERVER['SERVER_NAME'];
    }
    
    public function _getPort(){
        return $_SERVER['SERVER_PORT'];
    }
    
    public function _getRoot()
    {
        return dirname(Util::slashUrl($_SERVER['SCRIPT_NAME']));
    }
    
    public function _getPath() {
        $uri = Util::slashUrl($_SERVER['REQUEST_URI']);
        if (($pos = strpos($uri, '?')) > 0){
            $uri = substr($uri, 0, $pos);
        }
        $root = $this->root;
        if (($pos = strpos($uri, $root)) === 0){
            $uri = substr($uri, strlen($root), strlen($uri));
        }
        // hack index.php
        $index_php = '/index.php';
        if (($pos = stripos($uri, $index_php)) === 0){
            $uri = substr($uri, strlen($index_php), strlen($uri));
        }
        if (!$uri)
            $uri = '/';
        return $uri;
    }
    
    public function url($path = null) {
        if (is_null($path)) {
            $path = $this->path;
        }
        $r = $this->port == '443' ? 'https' : 'http';
        $r .= '://' . $this->host;
        $r .= $this->root;
        if ($path) {
            if (strpos($path, '/') !== 0){
                $path = '/' . $path;
            }
        }
        $r .= $path ? $path : '/';
        return $r;
    }
    
    public function option($key) {
        if (isset($this->_options[$key]))
            return $this->_options[$key];
    }
    
    public function setOptions($options) {
        if ($options) {
            $this->_options = array_replace($this->_options, $options);
        }
    }
    
    protected $_options = [
        'cookie_prefix' =>'',
    ];
}