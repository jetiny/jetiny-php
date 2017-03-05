<?php
namespace Jetiny;

class Loader
{
    public static $override;
    public static function autoload ($className)
    {
        $start = strpos($className, '\\');
        if ($start !== false) {// namespace
            $namespace = str_replace('\\', '/', $className);
            $fullPath = $namespace . '.php';
            $len = strlen($fullPath);
            if (static::$override) {//slow Jetiny\Base => Jetiny
//Jetiny\Loader::registerAutoloader();
//Jetiny\Loader::registerAutoloader('Jetiny/Filter', __DIR__ . '/Filter'); // can override Filter
                while ($start = strrpos($namespace, '/')) {
                    $namespace = substr($namespace, 0, $start);
                    $filepath  = substr($fullPath, $start+1, $len);
                    if (isset(static::$_maps[$namespace])) {
                        if (file_exists($filepath = static::$_maps[$namespace] . '/'. $filepath)) {
                            require $filepath;
                            break;
                        }
                    }
                }
            } else {//quick Jetiny => Jetiny\Base
                do {
                    $ns = substr($namespace, 0, $start);
                    $filepath = substr($fullPath, $start+1, $len);
                    if (isset(static::$_maps[$ns])) {
                        if (file_exists($filepath = static::$_maps[$ns] . '/'. $filepath)) {
                            require $filepath;
                            break;
                        }
                    }
                } while ($start = strpos($namespace, '/', $start +1));
            }
        } else {
            $fullPath = "/$className.php";
            foreach(static::$_paths as $it) {
                if (file_exists($it . $fullPath)) {
                    require $it . $fullPath;
                    break;
                }
            }
        }
    }
    
    static protected $_maps;
    static protected $_paths;
    
    public static function registerAutoloader($namespace = null, $path = null)
    {
        if (is_null($namespace)) {
            $namespace = __NAMESPACE__;
            if (is_null($path))
                $path = __DIR__;
        }
        if (!static::$_maps) {
            static::$_maps = array();
            spl_autoload_register(__CLASS__ . "::autoload");
        }
        if (is_array($namespace)) {
            foreach ($namespace as $k => $v) {
                static::$_maps[$k] = str_replace('\\', '/', $v) ;
            }
        } else {
            if ($path) {
                static::$_maps[$namespace] = str_replace('\\', '/', $path) ;
            } else {
                static::$_paths[] = str_replace('\\', '/', $namespace);
            }
        }
    }
}
