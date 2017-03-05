<?php
namespace Jetiny\Http;

class ResponseCookie extends Jetiny\Base\NormalizeSet
{
    
    public function send() {
        foreach($this->_datas as $k => $v) {
            setcookie($k, $v['value'], $v['expires'], $v['path'], $v['domain'], $v['secure'], $v['httponly']);
        }
    }
    
    public function set($key, $value, $prefix = null)
    {
        if (is_array($value)) {
            $cookieSettings = array_replace($this->_options, $value);
        } else {
            $cookieSettings = array_replace($this->_options, ['value' => $value]);
        }
        $this->_datas[$this->normalize($key, $prefix)] = $cookieSettings;
    }
    
    public function rm($key, $prefix = null)
    {
        if (parent::get($key, $prefix)) {
            $this->set($key, [ 'expires'=> - 86400000 , 'value' => ''], $prefix);
        } else {
            unset($this->_datas[$this->normalize($key, $prefix)]);
        }
    }
    
    public function option($key) {
        if (isset($this->_options[$key]))
            return  $this->_options[$key];
    }
    
    public function setOptions($options) {
        if ($options) {
            $this->_options = array_replace($this->_options, $options);
        }
    }
    
    protected $_options = [
        'value' =>'',
        'domain' => null,
        'path' => null,
        'expires' => null,
        'secure' => false,
        'httponly' => false
    ];
}
