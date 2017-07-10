<?php
/**
 * 公共函数类
 * @name	CommonFun.class.php
 * @author	zhanghui
 */
class CommonFun {
    
    /**
     * 创建redis数据库模型
     *
     * @param int $name		选择使用的数据库
     * @param string|array $config 数据库连接配置信息
     * @return object	redis数据库模型
     */
    public static function redis($name=0, $config=''){
        static $_model = array();
        $config || $config = Config::$config['REDIS_DB'];
        is_array($config) || $config = Config::$config[$config];
        
        $identify = md5(json_encode($config).$name);    
        if(isset($_model[$identify])){
            return $_model[$identify];
        }else {
            return $_model[$identify] = new RedisModel($config,$name);
        }
    }
}
?>