<?php
/**
 +-----------------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------------
 * @description		Redis 数据库操作模型
 * @author	张辉
 * @date	2017年07月07日
 * @version		1.0
 +-----------------------------------------------
 */

class RedisModel{

	//redis连接池
	protected static $linkPool = array();

	//当前连接数据库
	protected $dbName = 0;

	//当前数据库连接句柄
	protected $link = null;

	/**
	 * 创建redis连接
	 *
	 * @access public
	 * @param mixed $config		redis服务器连接信息
	 * @param int $dbName	使用的数据库
	 * @return void
	 */
	public function __construct($config,$dbName = ''){

		//解析制定配置项
		if(!is_array($config)){
			$config = Config::$config[$config];
		}

		//设定默认端口号
		if(!isset($config['PORT'])){
			$config['PORT'] = 6379;
		}

		//判断是否存在连接池
		$identify = md5($config['HOST'].$config['PORT']);
		if(!isset(self::$linkPool[$identify])){
			self::$linkPool[$identify] = new Redis();
			self::$linkPool[$identify]->connect($config['HOST'],$config['PORT']);

			//验证密码
			if($config['PWD']){
				self::$linkPool[$identify]->auth($config['PWD']);
			}
		}

		//保存当前连接
		$this->link = self::$linkPool[$identify];

		//判断使用数据库
		if(empty($dbName) && $dbName!==0){

			if(!empty($config['DB_NAME']) || $config['DB_NAME']===0){
				$this->dbName = $config['DB_NAME'];
			}else{
				$this->dbName = 0;
			}
		}else{
			$this->dbName = $dbName;
		}
	}

	/**
	 * 选择数据库
	 *
	 * @access public
	 * @param int $dbName 选择数据库
	 * @return void
	 */
	public function select($dbName){
		$this->dbName = empty($dbName) && $dbName!==0 ? 0 : $dbName;
	}

	/**
	 * 访问redis原生扩展方法
	 *
	 * @access public
	 * @param string $funName	扩展名称
	 * @param array $params		参数列表
	 * @return mixed
	 */
	public function __call($funName,$params){
		//选择数据库
		$this->link->select($this->dbName);
		try{
			return call_user_func_array(array($this->link,$funName),$params);
		}catch(Exception $e){
			Log::record(exceptionToArray($e));
			return false;
		}
	}
}