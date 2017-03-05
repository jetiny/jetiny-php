<?php
namespace Jetiny\Service;
use Jetiny\Base\Exception;

class ActionService extends \Jetiny\Base\Factory
{
    
    public function setup($context)
    {
        $this->context = $context;
        $this->setOptions($context->config->peek('action'));
    }
    
    //根据请求创建路由
    public function createRoute($req)
    {
        $app = $this->context;
        $mode = $this->option('mode');
        $queryMode = strpos($mode, 'query') !== FALSE;
        $pathMode  = strpos($mode, 'path' ) !== FALSE;
        $routeMode = strpos($mode, 'route' ) !== FALSE;
        
        // 路径
        $path = $req->path;
        // 获取默认类和方法
        list($class, $method) = $this->option('default');
        
        if ($queryMode && $path == '/') { // query mode
            // 获取Query模式参数
            $query = $this->option('query');
            $class  = $req->param->get($query[0], $class);
            $method = $req->param->get($query[1], $method);
            
            $route = $app->router->createRoute($req->method, $path);
            $route->setOption('action', "$class:$method"); // 'user:login'
            return $route;
        } 
        
        if ($pathMode) { // path mode
            // 路径模式 /user/login => /Class/Method
            $paths = preg_replace('/(^\/|\/$)/', '', $path);
            $paths = explode('/', $paths);
            if (($n = count($paths)) <= 2) {
                if ($paths[0])
                    $class = $paths[0];
                if ($n > 1 && $paths[1])
                    $method = $paths[1];
                $route = $app->router->createRoute($req->method, $path);
                $route->setOption('action', "$class:$method"); // 'user:login'
                return $route;
            }
        }
        
        if ($routeMode) {// route mode
            if ($matchs = $this->context->router->match($req->path, $req->method)) {
                list($route, $query) = $matchs;
                $route->setOption('query', $query);
                return $route;
            }
        }
    }
    
    // 根据路由创建 Action
    public function createAction($route)
    {
        if ($action = $route->option('action')) {
            list($class, $method) = explode(':', $action);
            if ($obj = $this->createObject($class)) {
                $class = get_class($obj);                        // user  => App\Action\User
                $method .= $this->option('method_suffix');       // login => loginAction
                $cv = "$class::$method";
                if (defined($cv)) {                              // const loginAction=""
                    if ($query = constant($cv))
                        $route->setOptions($route->parseQuery($query));
                } else {    // public static $loginAction = "" || []
                    $arr = get_class_vars($class);
                    if (isset($arr[$method])) {
                        $opts = is_array($arr[$method]) ? $arr[$method] : 
                            $route->parseQuery($arr[$method]);
                        $route->setOptions($opts);
                    }
                }
                if (method_exists($obj, $method)){
                    $route->setOption('closure', [$obj ,$method]);
                }
            }
        }
        if ($closure = $route->option('closure')) {
            return $closure;
        }
    }
    
    public function createObject($name)
    {
        if (isset($this->_classes[$name]))
            return $this->_classes[$name];
        if ($obj = parent::createObject($name)) {
            if (method_exists($obj, 'setup')) {
                $obj->setup($this->context);
            }
            return $this->_classes[$name] = $obj;
        }
    }
    
    protected $context;
    protected $_options = [
        'namespace'     => 'App\\Action\\',        //命名空间
        'class_suffix'  => 'Action',    //默认类名后缀
        'method_suffix' => '',    //默认方法名后缀
        //默认的类和方法名称
        'default' => ['index', 'index'],
        //Query模式下的URL映射配置 ?a=user&m=login => App\Action\UserAction::login
        'query' => ['a', 'm'],
        // URL模式 优先级为 route > query > path
        'mode' => 'query|path|route' // query|path|route 
    ];
}