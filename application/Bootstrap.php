<?php

/**
 *      [CodeJm!] Author CodeJm[codejm@163.com].
 *
 *      初始化, 引导程序
 *      $Id: Bootstrap.php 2017-07-04
 */
 
Yaf_loader::import(APP_PATH."library/vendor/autoload.php");
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

class Bootstrap extends \Yaf_Bootstrap_Abstract {

	protected $config;

    // 配置初始化
    public function _initConfig(\Yaf_Dispatcher $dispatcher) {
		Config::load();
		Yaf_Dispatcher::getInstance()->autoRender(FALSE);
    }


    // 错误处理
    public function _initError(\Yaf_Dispatcher $dispatcher) {
        Core_ErrorHandler::init();

    }/*}}}*/

    // 注册插件
    public function _initPlugin(\Yaf_Dispatcher $dispatcher) {
        
        // 初始化模版引擎 twig
        $Twig = new TwigPlugin();
        $dispatcher->registerPlugin($Twig);
        if(Config::$config['xhprof']['open']){
            $xhprof = new XhprofPlugin();
            $dispatcher->registerPlugin($xhprof);
        }
    }

    // 路由
    public function _initRoute(\Yaf_Dispatcher $dispatcher) {
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $route = array();

        // 默认进入index/index
        $modules = \Yaf_Application::app()->getModules();
        if($modules) {
            foreach ($modules as $module) {
                $name = strtolower($module);
                $route[$name] = new Yaf_Route_Rewrite(
                    '/('.$name.'|'.$name.'/|'.$name.'/index|'.$name.'/index/)$',
                    array(
                        'controller' => 'index',
                        'action' => 'index',
                        'module' => $name,
                    )
                );
            }
        }

        //使用路由器装载路由协议
        foreach ($route as $k => $v) {
            $router->addRoute($k, $v);
        }
        Yaf_Registry::set('rewrite_route', $route);
    }
    
    // 日志请求初始化
    public function _initLog(){
        //定义一个唯一请求值
        define('SN', uniqid(rand(), true));
    }

    //载入数据库ORM
    public function _initDatabase() {
        $database = array(
            'driver' => Config::$config['database']['dbtype'],
            'host' => Config::$config['database']['host'],
            'database' => Config::$config['database']['dbname'],
            'username' => Config::$config['database']['username'],
            'password' => Config::$config['database']['password'],
            'charset' => Config::$config['database']['charset'],
            'collation' => Config::$config['database']['collation'],
            'prefix' => ''
        );
        $capsule = new Capsule;
        // 创建链接
        $capsule->addConnection($database);
        // 设置全局静态可访问
        $capsule->setAsGlobal();
        // 启动Eloquent
        $capsule->bootEloquent();
    }
    

}

?>
