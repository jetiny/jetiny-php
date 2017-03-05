<?php

require './src/Loader.php';
\Jetiny\Loader::registerAutoloader();
\Jetiny\Loader::registerAutoloader('App', dirname(__FILE__));

$app = \Jetiny\App::instance();

$app->setOptions([
    'request' => [
        'xxx' => 'vvv',
    ],
    'response' => [
        'xxx' => 'vvv',
    ],
    'router' =>[
        'namespace' => '\App\Action',
        'routers' => [
            'get' =>[
                ['/', 'index:index']
            ]
        ]
    ],
]);

$act = new \Jetiny\Action ;

$res = $act->response;

$router = $act->router;

$router->get('/test', 'test:mmx');
$router->get('/test/:xxx', function(){});
/*$res->setBody(file_get_contents('autorunschs.exe'));
$res->setHeader('Content-Type', 'application/octet-stream');
$res->send();*/

$res->setStatusCode(200);
$res->setCookie('aaa-bbb', 'bbb');
$res->setHeader('aaa-bbb', 'bbb');
$res->setFormat('html');
$res->send();

$req = $act->request;
var_dump($req->method, $req->format, $req->host, $req->port, $req->root ,$req->path, $req->ajax);

var_dump($router->parse($req));
$req->path = '/test'; var_dump($router->parse($req));
$req->path = '/test/xxx'; var_dump($router->parse($req));


var_dump($req , $res, $router);