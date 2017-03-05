<?php
require '../Jet/Loader.php';
\Jetiny\Loader::registerAutoloader();
\Jetiny\Loader::registerAutoloader('App', __DIR__);
\Jetiny\Loader::registerAutoloader(__DIR__);
$app = \Jetiny\Application::instance();

$app->run();
