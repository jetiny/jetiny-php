<?php
return [
    //'ndebug' => FALSE, 
    'preloads' => [ // 预加载服务
        'BootstrapService',
        '\Jetiny\Service\FormatService',
    ],
    'services' => [ // 按需加载服务
        'SimpleService'
    ],
    'router' =>[
        'caseInsensitive' => TRUE
    ],
    'routers' => [
/*        'GET  /  	              ?action=index:index',
        
        'GET  /action_static      ?action=index:action_static',
        'GET  /action_public      ?action=index:action_public',
        'GET  /action_const       ?action=index:action_const',
        
        
        'GET  /test               ?action=index:test',
        'GET  /format/json        ?action=index:json    &format=json|jsonp',
        'GET  /header/302         ?action=index:header_302',
        'GET  /err/exception      ?action=index:err_exception',
        'GET  /err/fatal          ?action=index:err_fatal',
        'GET  /err/parse          ?action=index:err_parse',
        'GET  /session            ?action=index:session &format=json',*/
    ],
    'template' => [
        'path'=> __DIR__ . '/tpl/',
        
        'cache_tmpl' => true,            // enable cache compiled file
        'cache_debug' => true,           // enable cache file debug with codelobster etc.
        'cache_data' => false,           // enable cache compiled buffer
        
        'compress' => false,             // enable compress
		'cache_path'=> __DIR__ . '/tmp/template/',
    ],
    'cookie' => [
        'prefix' => 'ck_',
        'domain' => null,
        'path' => null,
        'expires' => null,
        'secure' => false,
        'httponly' => false ,
    ],
    'request' => [
        'cookie_prefix' => 'ck_',
    ],
    'action' => [ // Action 服务
        'namespace'     => 'App\\Action\\',        //命名空间
        'class_suffix'  => 'Action',    //默认类名后缀
        'method_suffix' => '',    //默认方法名后缀
        //默认的类和方法名称
        'default' => ['index', 'index'],
        //Query模式下的URL映射配置 ?a=user&m=login => App\Action\UserAction::login
        'query' => ['a', 'm'],
        // URL模式 优先级为 route > query > path
        'mode' => 'query|path|route' // query|path|route 
    ],
    'exception' => [
        'notice_as_exception' => FALSE,
        'exception_as_fatal'  => FALSE
    ],
    'format' => [
        'query' => 'format',    //可根据参数确定后缀格式 /?format=json
        'jsonp' => 'callback',
        'trust'=> 'html|json|jsonp',
    ],
    'lang' => [
        'local' => 'zh-CN',
        'path'  => __DIR__ . '/Lang/',
    ],
    'cache' => [
        'default' => [
            "name"  => 'mongoDB',
            "db" => "test",
            "collection" => "cache",
        ],
        'pool' => [
            [
                "name"  => 'xcache',
                "class" => 'Jetiny\\Cache\XcacheCache',
            ],
            [
                "name"  => 'file',
                "directory" => __DIR__ . '/tmp/cache',
            ]
        ]
    ],
];