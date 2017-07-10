<?php

/**
 *      [CodeJm!] Author CodeJm[codejm@163.com].
 *
 *      网站入口文件
 *      $Id: index.php 2014-07-27 12:04:10 codejm $
 */

// 常用目录定义
/* {{{ */
date_default_timezone_set('PRC');
header('Content-type: text/html; charset=utf-8');
define('DS', '/');
define('PUBLIC_PATH', dirname(__FILE__).DS);
define('BASE_PATH', realpath(dirname(__FILE__).DS.'..').DS);
define('APP_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'application').DS);
/* }}} */

// composer
require_once BASE_PATH.'vendor/autoload.php';

// 框架入口
$app  = new Yaf_Application(APP_PATH.'conf/application.ini');

$app->bootstrap()->run();

?>
