<?php

namespace Jetiny;

class Request
{
    public function setup()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->init();
    }
    public function setOptions($value) {
        $this->_options = array_replace_recursive($this->_options, $value);
    }
    protected function init() {
        $this->_GET     = $_GET;
        $this->_POST    = $_POST;
        $this->_REQUEST = $_REQUEST;
        $this->_FILES   = $_FILES;
        $this->_ENV     = $_ENV;
        $this->_SERVER  = $_SERVER;
        $this->_HEADER  = $this->_extractHeader($_SERVER);
        $this->_SESSION = $_SESSION;
        $this->_COOKIE  = $_COOKIE;
    }
    public function get($key = NULL, $default = NULL) {
        return $this->_lookup($this->_GET, $key, $default);
    }
    public function post($key = NULL, $default = NULL) {
        return $this->_lookup($this->_POST, $key, $default);
    }
    public function file($key = NULL, $default = NULL) {
        return $this->_lookup($this->_FILES, $key, $default);
    }
    public function request($key = NULL, $default = NULL) {
        return $this->_lookup($this->_REQUEST, $key, $default);
    }
    public function header($key = NULL, $default = NULL) {
        return $this->_lookup($this->_HEADER, $key, $default);
    }
    public function session($key = NULL, $default = NULL) {
        return $this->_lookup($this->_SESSION, $key, $default);
    }
    public function cookie($key = NULL, $default = NULL) {
        return $this->_lookup($this->_COOKIE, $key, $default);
    }
    public function server($key = NULL, $default = NULL) {
        return $this->_lookup($this->_SERVER, $key, $default);
    }
    public function env($key = NULL, $default = NULL) {
        return $this->_lookup($this->_ENV, $key, $default);
    }
    protected function _lookup($arr, $key, $default) {
        if ($key === NULL)
            return $arr;
        if (is_array($key)) {
            $r = [];
            foreach($key as $k) {
                $r[$k] = array_key_exists($k, $arr) ? $arr[$k] : $default;
            }
            return $r;
        }
        return array_key_exists($key, $arr) ? $arr[$key] : $default;
    }
    protected function _extractHeader($arr) {
        $results = array();
        foreach ($arr as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $results[$this->normalizeHeader(substr($key, 5, strlen($key)))] = $value;
            }
            else if (strpos($key, 'X_') === 0) {
                $results[$this->normalizeHeader(substr($key, 2, strlen($key)))] = $value;
            }
        }
        return $results;
    }
    protected function normalizeHeader($key) {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        //$key = preg_replace('#^http #', '', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);
        return $key;
    }
    
    public function __get($key) {
        if (method_exists($this, $m = '_get' . ucwords($key))) {
            return $this->{$key} = $this->{$m}();
        }
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
        if ($format = $this->header('ACCEPT') ) {
            $format = static::getFormat($format);
        }
        return $format ? $format : 'HTML';
    }
    
    public function _getAjax()
    {
        return $this->header('Requested-With')== 'XMLHttpRequest';
    }
    
    public function _getHost(){
        return $this->server('SERVER_NAME');
    }
    
    public function _getPort(){
        return $this->server('SERVER_PORT');
    }
    
    public function _getJsonp(){
        return $this->request($this->_options['jsonp']);
    }
    
    public function _getRoot()
    {
        return dirname(static::slashUrl($this->server('SCRIPT_NAME')));
    }
    
    public function _getPath() {
        $uri = static::slashUrl($this->server('REQUEST_URI'));
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
    
    static function slashUrl($url) {// 'REQUEST_URI', 'SCRIPT_NAME', 'PHP_SELF'
        return preg_replace('/\/+/', '/', $url);
    }
    protected $_options = [
        'jsonp' => 'callback',
    ];
    protected static $_formats;
    protected static function initializeFormats() {
        static::$_formats = array(
            'HTML' => array('text/html', 'application/xhtml+xml'),
            'JSON' => array('application/json', 'application/x-json', 'text/json'),
            'XML'  => array('text/xml', 'application/xml', 'application/x-xml'),
            'JS'=> array('application/javascript', 'application/x-javascript', 'text/javascript'),
            'TXT'  => array('text/plain'),
            'FORM' => array('application/x-www-form-urlencoded'),
        );
    }
}
