<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		日志管理类
 * @author	张辉	
 * @date 2017年07月06日
 * @version	 1.0
 +-----------------------------------------
 */

class Log {

	// 日志临时记录空间
	public static $logs = array ();

	// 日志驱动目录
	private static $drivePath = 'Log_';
    
	/**
	 * 记录日志
	 *
	 * @static
	 * @access public
	 * @param string|array $message
	 * @param string $level
	 * @param boolean $record	是否强制记录
	 * @param boolean $backtrace
	 * @return void
	 */
	public static function record($message, $level='ERROR', $record = false, $backtrace = false) {
	    
		if ($record || false !== stripos ( Config::$config['LOG']['LOG_LEVEL'], $level )) {
			if (! is_array ( $message )) {
				$message = array (
						'message' => $message
				);
			}
			defined('SN') && $message ['sn'] = SN;
			$message ['create_time'] = time ();
			$message ['level'] = strtolower($level);
			($backtrace || false !== stripos ( Config::$config['LOG']['LOG_LEVEL_BACKTACE'], $level )) && $message ['backtrace'] = json_encode ( debug_backtrace () );
			self::$logs [] = $message;
		}
	}

	/**
	 * 日志保存
	 *
	 * @static
	 * @access public
	 * @param integer $type		日志记录方式
	 * @return void
	 */
	public static function save($type = '') {
		if(empty(self::$logs)){
			return true;
		}
        $LOG_STORAGE = json_decode(Config::$config['LOG']['LOG_STORAGE'],true);
        
		if (empty ( $type )) {
			 $type =  $LOG_STORAGE[0];
		}
		
		$storage = self::$drivePath.$type.'Log';
		if ( false !== stripos ( Config::$config['LOG']['LOG_STORAGE'], $type ) ) {
				$writeStatus = $storage::save ( self::$logs );
		} else {
			$writeStatus = false;
		}

		//根据日志驱动级别选择
		if (! $writeStatus) {
			$key = array_search ( $type, $LOG_STORAGE );
			if ($key !== false && isset ( $LOG_STORAGE[$key + 1] )) {
				self::save ( $LOG_STORAGE[$key + 1] );
			}
		}
		self::$logs = array();
	}

	/**
	 * 日志直接写入
	 *
	 * @static
	 * @access public
	 * @param mixed $message
	 * @param string $level
	 * @param string $type
	 * @param boolean $backtrace	是否强制记录错误回溯信息
	 * @return boolean
	 */
	public static function write($message, $level='ERROR', $type = '', $backtrace = false) {
		if (empty ( $type )) {
			$type = json_decode(Config::$config['LOG']['LOG_STORAGE'],true)[0];
		}
		
		if (! is_array ( $message )) {
			$message = array (
				'message' => $message
			);
		}

		$message ['sn'] = SN;
		$message ['create_time'] = time ();
		$message ['level'] = strtolower($level);
		($message ['backtrace'] = $backtrace || false !== stripos ( Config::$config['LOG']['LOG_LEVEL_BACKTACE'], $level )) && json_encode ( debug_backtrace () );

		$storage = self::$drivePath.$type.'Log';
		if ( false !== stripos ( Config::$config['LOG']['LOG_STORAGE'], $type ) ) {
			return $storage::save ( array($message) );
		}
		
		return false;
	}
}