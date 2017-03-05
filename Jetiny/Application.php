<?php

namespace Jetiny;

use \Jetiny\Base\Exception;

class Application extends \Jetiny\Service\KenelService
{
    static public function instance()
    {
        static $_instance;
        if (!$_instance) {
            $_instance = new static;
        }
        return $_instance;
    }
    
    protected function __construct() {
        
    }
    
    public function setConfigFile($value)
    {
        $this->_configFile = $value;
    }
    
    public function configFile()
    {
        if (isset($this->_configFile))
            return $this->_configFile;
        return $this->_configFile = dirname($_SERVER['SCRIPT_FILENAME']) .'/config.php';
    }
    
    public function run()
    {
        $this->setup();
        $this->execute();
    }
    
    public function execute()
    {
        try {
            $req = $this->request;
            // apply request filters
            $this->walkService(function($it) use(&$req){
                if (method_exists($it, 'request'))
                    $it->request($req);
            });
            if ($route = $this->action->createRoute($req)) {
                if ($this->action->createAction($route)) {
                    // apply route filters
                    $this->walkService(function($it) use(&$route, &$req){
                        if (method_exists($it, 'route'))
                            $it->route($route, $req);
                    });
                    if (!isset($this->route))
                        $this->route = $route;
                    if ($res =  call_user_func($route->option('closure'))) {
                        // apply response filters
                        $this->walkService(function($it) use(&$res){
                            if (method_exists($it, 'response'))
                                $it->response($res);
                        });
                        return $res->send();
                    }
                }
            }
            Exception::throwError('err.404', $req->path);
        } catch (\Exception $e) {
            Exception::setLastError($e);
        }
    }
    
    public function terminate() {
        // terminate
        if ($this->_terminate)
            return ;
        $this->_terminate = TRUE;
        // exception
        
        // flush
        if (function_exists('fastcgi_finish_request')){
            fastcgi_finish_request();
        }
        // teardown
        $this->teardown();
    }
    protected $_configFile;
    protected $_terminate;
    //preload services
    protected $preloads = [
        'exception'     => '\Jetiny\Service\ExceptionService',
        'format'        => '\Jetiny\Service\FormatService',
        'validate'      => '\Jetiny\Service\ValidateService',
        'lang'          => '\Jetiny\Service\LangService',
    ];
    protected $servicePool = [
        'config'        => '\Jetiny\Service\ConfigService',
        'template'      => '\Jetiny\Service\TemplateService',
        'router'        => '\Jetiny\Service\RouterService',
        'action'        => '\Jetiny\Service\ActionService',
        'request'       => '\Jetiny\Service\RequestService',
        'cookie'        => '\Jetiny\Service\CookieService',
        'header'        => '\Jetiny\Service\HeaderService',
        'cache'         => '\Jetiny\Service\cacheService',
    ];
}
