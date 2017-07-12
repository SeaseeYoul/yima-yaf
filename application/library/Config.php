<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		Config配置管理类
 * @author	张辉 961518686@qq.com
 * @date 2017年07月07日
 * @version	 1.0
 +-----------------------------------------
 */

class Config {
	// 配置选项
	public static $config = array ();
	
	// 加载配置文件
	public static function load(){
	    self::$config =  Yaf_Application::app()->getConfig()->toArray();
	    self::$config = array_merge(self::$config,(array) include APP_PATH."conf/common.config.php");
	}

}