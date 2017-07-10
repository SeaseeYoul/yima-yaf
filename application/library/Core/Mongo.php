<?php
/**
 +-----------------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------------
 * @description		MongoDb 数据库操作模型
 * @author	张辉    961518686@qq.com
 * @date	2017年07月07日
 * @version		1.0
 +-----------------------------------------------
 */

class Core_Mongo{

	//redis连接池
	protected static $linkPool = array();

	//当前连接数据库
	protected $dbName = 0;

	//当前操作的数据表
	protected $table = 0;

	//当前数据库连接句柄
	protected $link = null;

	//当前数据库配置
	protected $config = array();

	/**
	 * 创建mongo数据库连接
	 *
	 * @access public
	 * @param string $name		模型名称
	 * @param string $config	数据库连接配置信息
	 * @return void
	 */
	public function __construct($name,$config=''){
		$this->table = $name;
		if ($config){
			$this->config = $config;
		}else{
			$this->config = Config::$config['MONGO_DB'];
		}
		$this->initConnect();
	}

	/**
	 * 连接数据库
	 *
	 * @access public
	 * @param int $retry	重连次数
	 * @return void
	 */
	protected function initConnect($retry=3) {
	    
		$identify = md5(json_encode($this->config));
		
		if(!isset(self::$linkPool[$identify])){
			$host = 'mongodb://'.($this->config['DB_USER'] ? "{$this->config['DB_USER']}:{$this->config['DB_PWD']}@" : '').$this->config['DB_HOST'].($this->config['DB_PORT']?":{$this->config['DB_PORT']}":'27017').'/'.($this->config['DB_NAME']?"{$this->config['DB_NAME']}":'');
			try{
				self::$linkPool[$identify] = new MongoClient($host);
			}catch (MongoConnectionException $e){
				if($retry > 0){
					$this->initConnect(--$retry);
				}else{
					Log::record($e->getmessage(), 'ERROR');
				}
			}
		}
		$this->dbName = $this->config['DB_NAME'];
		return $this->link = self::$linkPool[$identify];
	}

	/**
	 * 切换数据库
	 *
	 * @access public
	 * @param string $db	数据库名称
	 * @return object
	 */
	public function selectDB($db){
		$this->dbName = $db;
		return $this;
	}

	/**
	 * 切换数据表
	 *
	 * @access public
	 * @param string $table		数据库表名称
	 * @return object
	 */
	public function selectCollection($table){
		$this->table = $table;
		return $this;
	}

	/**
	 * 执行数据库操作
	 *
	 * @access public
	 * @param string $funName	扩展名称
	 * @param array $params		参数列表
	 * @return mixed
	 */
	public function __call($funName,$params){
		$dbName = $this->dbName;
		$table = $this->table;
		//执行操作
		try{
			$mongo = $this->link->$dbName->$table;
			return call_user_func_array(array($mongo,$funName),$params);
		}catch (Exception $e){
			Log::record($e->getMessage(), 'ERROR');
			return false;
		}
	}
}