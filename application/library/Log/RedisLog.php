<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		Redis日志存储驱动
 * @author	张辉
 * @date 2017年07月07日
 * @version		1.0
 +-----------------------------------------
 */

class Log_RedisLog{

	/**
	 * Redis日志存储
	 *
	 * @static
	 * @access public
	 * @param  array $config 文件日志配置
	 * @param  array $logs   文件日志内容
	 * @return boolean
	 */
	public static function save($logs,$config=''){
		$config || $config = Config::$config['REDIS'];
		$redis = CommonFun::redis($config['DB_LOG_NAME'],$config);
		if($redis->rPush($config['RANK_NAME'],json_encode($logs))){
			return true;
		}else{
			return false;
		}
	}
}