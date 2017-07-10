<?php
/**
 * Api共有模块
 * @name   	Core_ApiBase.class.php
 * @author 张辉
 */
class Core_ApiBase extends \Yaf_Controller_Abstract  {
    /**
     * 请求开始时间
     * @var float
     */
    private $request_start_time = 0;
    
    /**
     * 请求结束时间
     * @var float
     */
    private $request_end_time = 0;
    
    /**
     * 客户端类型 android, iso, h5
     * @var string
     */
    protected $m_client_type = '';
    
    /**
     * 客户端唯一编号
     * @var string
     */
    protected $m_client = '';
    
    /**
     * 客户端版本
     * @var string
     */
    protected $m_version = '';
    
    /**
     * App 类型
     */
    protected $m_app_type = 'meiti';
    
    /**
     * 接口验证密钥
     * @var string
     */
    protected $m_sign_key = 'oxs*)ef$d@og].^c';
    
    /**
     * 登陆验证的session_key的hash串
     * @var string
     */
    protected $m_session_key_hash = 'A2T4Q9N1BZUH7MRVDG05KSLWPX86OCFJI3YE';
    
    /**
     * session起始时间截 2014-01-01 00:00:00
     * @var int
     */
    protected $m_session_stime = 1388505600;
    
    /**
     * session生存周期 24小时
     * @var int
     */
    protected $m_session_expire = 604800;
    
    /**
     * 登录用户编号
     * @var int
     */
    protected $m_login_userid = 0;
    
    /**
     * 登录用户信息, 包含下列字段
     *  'manage_id',
     *  'role_id',
     *  'company_id',
     *  'group_id',
     *  'manage_name',
     *  'nickname',
     *  'realname'
     * @var array
     */
    protected $m_login_user_base_info = array();
    
    /**
     * 参数集合
     * @var array
     */
    protected $param = array();
    
    /**
     * Memcache配置名称
     * @var string
     */
    protected static $memcache = 'default';
    
    /**
     * Redis配置名称
     * @var string
     */
    protected static $redis = 'default';
    
    /**
     * 初始化函数
     */
    public function init() { 
         // 设置请求开始时间
         $this->request_start_time = microtime(true);
        
         // 合并请求参数
         $get = Tools_help::filter($_GET);
         $post = Tools_help::filter($_POST);
         $this->param = array_merge($get, $post);
         
        // 验证必传参数
        $client = isset($this->param['client']) ? trim($this->param['client']) : '' ;
        if ( !$client ) {
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'client'));
        }
        if ( substr($client, 0, 4)!='ios_' && substr($client, 0, 8)!='android_' && substr($client, 0, 3)!='h5_' && substr($client, 0, 3)!='wx_' ) {
            // 必须是ios_开头或者是android_开头的标识或者h5_开头的标识
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'client'));
        }
        
        // 客户端唯一编号
        $this->m_client = str_replace(array('ios_', 'android_', 'h5_', 'wx_'), array('', '', '', ''), $client);
        if ( !$this->m_client ) {
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'client'));
        }
        
        // 客户端类型
        if ( substr($client, 0, 4)=='ios_' ) {
            $this->m_client_type = 'ios';
        }
        elseif ( substr($client, 0, 8)=='android_' ){
            $this->m_client_type = 'android';
        }
        elseif ( substr($client, 0, 3)=='h5_' ){
            $this->m_client_type = 'h5';
        }
        elseif ( substr($client, 0, 3)=='wx_' ){
            $this->m_client_type = 'wx';
        }
        
        // 版本号
        $version = isset($this->param['version']) ? trim($this->param['version']) : '' ;
        if ( !$version ) {
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'version'));
        }
        if ( substr($version, 0, 4)!='ios_' && substr($version, 0, 8)!='android_' && substr($version, 0, 3)!='h5_' && substr($client, 0, 3)!='wx_' ) {
            // 必须是ios_开头或者是android_开头的标识或者h5_开头的标识
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'version'));
        }
        $this->m_version = str_replace(array('ios_', 'android_', 'h5_', 'wx_'), array('', '', '',''), $version);
        if ( !$this->m_version ) {
            $this->api_error(ErrorConfig::$ERROR_REQUEST_REQUIRED_PARAMS_ERROR, array('p'=>'version'));
        }
        
        // 验证登录 , 没有登录直接返回'需要登录'
//         $not_login_module_action = C('NOT_LOGIN_MODULE_ACTION');
//         if ( !$not_login_module_action || !isset($not_login_module_action[MODULE_NAME]) || !in_array(ACTION_NAME, $not_login_module_action[MODULE_NAME]) ) {
//             $this->get_login_user(true);	
//             //$this->user_login_log_redis();
//         }
        
    }
    
    /**
     * 生成用户的session_key
     * @param int $userid	用户编号
     * @return string
     */
    protected function generate_session_key($userid) {
        $userid = (int) $userid;
        $now_time = time();
        $diff_time = $now_time-$this->m_session_stime;
        $m_session_key = $this->m_session_key_hash;
        $hash = md5($userid."@".$now_time."@".$m_session_key);
        $session_key = $diff_time."_".$userid."_".$hash;
        return $session_key;
    }
    
    /**
     * 获取登录用户信息
     * @param 	bool $exit	如果未登录是否直接退出报错，如果true的话，在未登录情况下，直接返回错误输出
     * @return 	bool|array
     */
    protected function get_login_user($exit=true){
        if ( $this->m_login_userid && $this->m_login_user_base_info ) {
            return true;
        }
    
        $session_key = isset($this->param['session_key']) ? $this->param['session_key'] : '' ;
        if ( !$session_key ) {
            if ( $exit ) {
                $this->api_error(ErrorConfig::$ERROR_NEED_LOGIN);
            }
            return false;
        }
    
        $key_a = explode("_", $session_key);
        if ( count($key_a)!=3 ) {
            if ( $exit ) {
                $this->api_error(ErrorConfig::$ERROR_NEED_LOGIN);
            }
            return false;
        }
    
        $time = intval($key_a[0]);
        $userid = intval($key_a[1]);
        $hash = $key_a[2];
        $time += $this->m_session_stime;
    
        $m_session_key = $this->m_session_key_hash;
    
        $co_hash = md5($userid."@".$time."@".$m_session_key);
        if ( $co_hash!=$hash ) {
            if ( $exit ) {
                $this->api_error(ErrorConfig::$ERROR_NEED_LOGIN);
            }
            return false;
        }
    
        // @todo: 可以对session_key的有效期进行验证
        // 通过session_key从memcache中获取数据, 如果没有, 也判定需要登录
        $session_data = $this->get_session_memcache_data($session_key);
        if ( !$session_data ) {
            if ( $exit ) {
                $this->api_error(ErrorConfig::$ERROR_NEED_LOGIN);
            }
            return false;
        }
        // @todo: 存储一个user_id和session_key的关系, 如果session_key变化, 说明发生'异地'登录
        $this->m_login_userid = (int) $session_data['manage_id'];
        $this->m_login_user_base_info = $session_data;
        if ( $userid!=$this->m_login_userid ) {
            if ( $exit ) {
                $this->api_error(ErrorConfig::$ERROR_NEED_LOGIN);
            }
            return false;
        }
    
        return $this->m_login_user_base_info;
    }
    
    /**
     * 设置session数据
     * @param string $session_key   SESSION KEY
     * @param array  $data			数据
     * @return bool
     */
    protected function set_session_memcache_data($session_key, array $data) {
        $session_key = trim($session_key);
        if ( !$session_key ) {
            return false;
        }
        if ( !$data ) {
            return false;
        }
        $cache_obj = CommonFun::get_memcache(self::$memcache);
        // 先获取数据
        $old_data = $cache_obj->get($session_key);
        if ( $old_data ) {
            $data = array_merge($old_data, $data);
        }
        $cache_obj->set($session_key, $data, $this->m_session_expire);
        return true;
    }
    
    /**
     * 获取session数据
     * @param string $session_key   SESSION KEY
     * @return array
     */
    protected function get_session_memcache_data($session_key) {
        $session_key = trim($session_key);
        if ( !$session_key ) {
            return false;
        }
        $cache_obj = CommonFun::get_memcache(self::$memcache);
        $data = $cache_obj->get($session_key);
        return $data ? $data : array() ;
    }
    
    /**
     * 清除session数据
     * @param string $session_key	SESSION KEY
     * @return boolean
     */
    protected function remove_session_memcache_data( $session_key ) {
        $session_key = trim($session_key);
        if ( !$session_key ) {
            return false;
        }
        $cache_obj = CommonFun::get_memcache(self::$memcache);
        $cache_obj->rm($session_key);
    }
    
    /**
     * 接口错误返回方法
     * @param array 	$error_config	错误配置, 来自ErrorConfig.class.php
     * @param array 	$replace		替换error中的字符串标示
     * @param array		$data			返回的数据
     * @param string 	$exit			是否输出后执行exit()
     */
    protected function api_error( array $error_config, array $replace=array(), array $data=array(), $exit=true) {
        if ( $error_config ) {
            $errno = $error_config['errno'];
            $error = $error_config['error'];
            if ( $replace ) {
                foreach($replace as $k=>$v){
                    $key = "{".$k."}";
                    $error = str_replace($key, $v, $error);
                }
            }
        }else{
            $errno = -1;
            $error = '操作失败';
        }
    
        $this->api_return($error, $errno, $data, $exit);
    }
    
    /**
     * 接口返回
     * @param string $error
     * @param int $errno
     * @param array $data
     * @param bool $exit
     */
    protected function api_return( $error, $errno=-1, array $data=array(), $exit=false ) {
        // 设置请求结束时间
        $this->request_end_time = microtime(true);
        $api_cost_time = sprintf('%.4f', $this->request_end_time - $this->request_start_time);
        $errno = intval($errno);
        $ret = array(
            'errno'=>$errno,
            'error'=>$error,
            'result'=>$data ? $data : new stdClass(),
            'exec_time'=>$api_cost_time,
            'server_time'=>time(),
            'server'=>isset($_SERVER['HOSTNAME'])?$_SERVER['HOSTNAME']:""
        );
        
        $result = json_encode($ret);
        var_dump($ret);
        echo $result;die;
        if ( isset($_REQUEST['jsonp_callback']) && $_REQUEST['jsonp_callback'] ) {
            header('Content-Type:application/json; charset=utf-8');
            echo $_REQUEST['jsonp_callback'].'('.$result.');';
        }
        else{
            header('Content-Type:application/json; charset=utf-8');
            echo $result;
        }
    
        $is_trace = isset($_REQUEST['is_trace']) && $_REQUEST['is_trace'] ? 1 : 0 ;
        
        if ( $exit ){
            exit();
        }
        return true;
    }
    
    /**
     * 接口成功返回方法
     * @param array $data 	返回的数据
     * @param string $exit	是否输出后执行exit()
     */
    protected function api_success( array $data, $exit=true ) {
        //日志记录用户返回数据----@todo 记录到Api return
        //$this->user_return_log_redis($data);
        $errno = 0;
        $error = '';
        $this->api_return($error, $errno, $data, $exit);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
?>