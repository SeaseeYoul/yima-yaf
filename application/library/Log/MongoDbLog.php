<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		Mongo日志驱动
 * @author	戚银	thinkercode@sina.com
 * @date 2014年11月14日
 * @version		1.0
 +-----------------------------------------
 */

class Log_MongoDbLog{

	/**
	 * Mongo日志存储
	 *
	 * @static
	 * @access public
	 * @param  array $config 文件日志配置
	 * @param  array $logs   文件日志内容 
	 * @return boolean 
	 */
	public static function save($logs,$config=''){
		$config || $config = Config::$config['MONGO_DB'];
		$mongodb = new Core_Mongo('',$config);
		foreach($logs as $log){
			$mongodb->selectCollection(strtolower($log['level']));
			if(!$mongodb->insert($log)){
				return false;
			}
		}
		return true;
	}
}