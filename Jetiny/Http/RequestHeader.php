<?php
namespace Jetiny\Http;

class RequestHeader extends \Jetiny\Base\NormalizeSet
{
    
    public function text($key, $prefix = null)
    {
        return $key . ':' . $this->get($key, $prefix);
    }
    
    protected function normalize($key, $prefix)
    {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        $key = preg_replace('#^http #', '', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);
        return parent::normalize($key, $prefix);
    }
    
    static public function extract($data, $includes=[])
    {
        $results = array();
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, $includes)) {
                $results[$key] = $value;
            }
        }
        return $results;
    }
}
