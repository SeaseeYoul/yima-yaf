<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		错误处理类
 * @author	张辉	
 * @date 2017年07月10日
 * @version	 1.0
 +-----------------------------------------
 */

class Core_ErrorHandler {

	// 错误级别映射
	protected static $errLevelMap = array (
			E_ERROR => 'ERROR', 					// 致命的运行错误
			E_PARSE => 'ERROR', 					// 编译时解析错误。解析错误只由分析器产生
			E_CORE_ERROR => 'ERROR',				// PHP启动时初始化过程中的致命错误
			E_COMPILE_ERROR => 'ERROR', 			// 编译时致命性错。这就像由Zend脚本引擎生成了一个E_ERROR
			E_USER_ERROR => 'ERROR', 				// 用户自定义的错误消息
			E_WARNING => 'WARNING', 				// 运行时警告(非致命性错误)。非致命的运行错误，脚本执行不会停止
			E_CORE_WARNING => 'WARNING', 			// PHP启动时初始化过程中的警告(非致命性错)。
			E_COMPILE_WARNING => 'WARNING',			// 编译时警告(非致命性错)。
			E_USER_WARNING => 'WARNING', 			// 用户自定义的警告消息。
			E_NOTICE => 'NOTICE', 					// 运行时提醒(这些经常是你代码中的bug引起的，也可能是有意的行为造成的。)
			E_USER_NOTICE => 'NOTICE', 				// 用户自定义的提醒消息
			E_STRICT => 'STRICT'  					// 编码标准化警告
	);

	// FirePHP错误级别映射
	protected static $FirePHPMap = array (
			'ERROR' => 'ERROR',
			'WARNING' => 'WARN',
			'NOTICE' => 'INFO',
			'STRICT' => 'INFO'
	);

	/**
	 * 错误处理初始化方法
	 *
	 * @static
	 * @access public
	 * @return void
	*/
	public static function init() {

		// 捕获意外终止程序的错误信息
		register_shutdown_function ( array ('Core_ErrorHandler','fatalError') );

		// 自定义错误处理方式
		set_error_handler ( array ('Core_ErrorHandler','appError') );

		// 捕获没有try{}的异常信息
		set_exception_handler ( array ('Core_ErrorHandler','appException') );
	}

	/**
	 * 捕获意外终止程序的错误信息
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function fatalError() {
		$error = error_get_last ();
		if (isset ( self::$errLevelMap [$error ['type']] ) && false !== strpos ( Config::$config ['ERROR']['ERROR_TRAP_LEVEL'], self::$errLevelMap [$error ['type']] )) {
			if (Config::$config['application']['showErrors']) {
				self::showError ( $error );
			} else {
				Log::record ( $error, self::$errLevelMap [$error ['type']] );
			}
		}
		if (Config::$config['application']['showErrors']) {
			self::showError ();
		}

		if(in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR))){
// 			ob_end_clean();
			echo Config::$config ['ERROR']['ERROR_SHOW_CONTENTS'];
			Log::save();
		}
	}

	/**
	 * 自定义错误处理方式
	 *
	 * @static
	 * @access public
	 * @param int $type				错误类型
	 * @param string $message     	错误信息
	 * @param string $file     		错误文件
	 * @param int $line   			错误行数
	 * @return void
	 */
	public static function appError($type, $message, $file, $line) {

		if (isset ( self::$errLevelMap [$type] ) && false !== strpos ( Config::$config ['ERROR']['ERROR_TRAP_LEVEL'], self::$errLevelMap [$type] )) {

			$error = array ('type' => $type,'message' => $message,'file' => $file,'line' => $line);
			if (Config::$config ['ERROR']['FIRE_PHP_ON']) {
				self::showError ( $error );
			} else {
				Log::record ( $error, self::$errLevelMap [$error ['type']] );
			}
		}
	}

	/**
	 * 捕获没有try{}的异常信息
	 *
	 * @static
	 * @access public
	 * @param mixed $e		异常对象
	 * @return void
	 */
	public static function appException($e) {
		$error = array ();
		$error ['message'] = $e->getMessage ();
		$error ['type'] = $e->getCode ();
		$error ['type'] || $error ['type'] = E_ERROR;
		$trace = $e->getTrace ();

		if ('E' == $trace [0] ['function']) {
			$error ['file'] = $trace [0] ['file'];
			$error ['line'] = $trace [0] ['line'];
		} else {
			$error ['file'] = $e->getFile ();
			$error ['line'] = $e->getLine ();
		}

		if (isset ( self::$errLevelMap [$error ['type']] ) && false !== strpos ( Config::$config ['ERROR'] ['ERROR_TRAP_LEVEL'], self::$errLevelMap [$error ['type']] )) {
			if (Config::$config ['ERROR']['FIRE_PHP_ON']) {
				self::showError ( $error );
			} else {
				Log::record ( $error, self::$errLevelMap [$error ['type']] );
			}
		}
	}

	/**
	 * 捕获没有try{}的异常信息
	 *
	 * @static
	 * @access public
	 * @param array $error	记录错误信息，默认为空表示输出错误信息
	 * @return void
	 */
	public static function showError($error = null) {
		static $_trace = array ();
		if (is_null ( $error )) {
			$content = ob_get_clean();
			if($content){
				$logs = array();
				foreach ( $_trace as $error ) {
					$logs[] = "[" . self::$errLevelMap [$error ['type']] . ":{$error['type']}]] {$error['message']} file:{$error['file']} {$error['line']}行";
				}
// 				$content = preg_replace('/\}$/', ',logs:'.json_encode($logs).'}', $content);
			}else{
				foreach ( $_trace as $error ) {
					echo "<br/>[" . self::$errLevelMap [$error ['type']] . ":{$error['type']}]] {$error['message']} file:{$error['file']} {$error['line']}行";
				}
			}
			echo $content;
			$_trace = array ();
			return;
		}

		if(Config::$config ['ERROR']['FIRE_PHP_ON']){  
 			$type = self::$FirePHPMap[self::$errLevelMap [$error ['type']]] ? self::$FirePHPMap[self::$errLevelMap [$error ['type']]] : 'INFO';
 			FirePHP_ChromePhp::log($error);
 			return;
		}

		$_trace [] = $error;
	}
}