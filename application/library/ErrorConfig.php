<?php
/**
 * 公共错误配置库
 * @name	ErrorConfig.class.php
 * @author	lilei <lilei@kudianbao.com>
 */
class ErrorConfig {
	// 如需使用error替换标示请使用 {标示}
	
	/**
	 * 需要登录才能操作
	 * @var array
	 */
	static $ERROR_NEED_LOGIN = array('errno'=>101001, 'error'=>'需要登录才能操作');
	
	/**
	 * 必传参数{参数}错误
	 * @var array
	 */
	static $ERROR_REQUEST_REQUIRED_PARAMS_ERROR = array('errno'=>101002, 'error'=>'必传参数[{p}]错误');
	
	/**
	 * 某某参数错误
	 * @var array
	 */
	static $ERROR_REQUEST_PARAMS_ERROR = array('errno'=>101003, 'error'=>'[{p}]参数错误');
	
	/**
	 * 登录失败
	 * @var array
	 */
	static $ERROR_LOGIN_BAD = array('errno'=>101004, 'error'=>'用户名密码错误');
	
	/**
	 * 账户禁用
	 * @var array
	 */
	static $ERROR_USER_DISABLED = array('errno'=>101005, 'error'=>'账户已被禁用');
	
	/**
	 * 配置文件不存在
	 * @var array
	 */
	static $ERROR_MISS_CONFIG = array('errno'=>101006, 'error'=>'配置文件不存在');
	
	/**
	 * 某某未不可用
	 * @var array
	 */
	static $ERROR_CANNOT_STATE = array('errno'=>101007, 'error'=>'[{p}]状态不可用');
	
	/**
	 * 店铺不可用
	 * @var array
	 */
	static $ERROR_BRANCH_DISABLED = array('errno'=>101008, 'error'=>'店铺不可用');
	
	/**
	 * 集团不可用
	 * @var array
	 */
	static $ERROR_GROUP_DISABLED = array('error'=>101009, 'error'=>'集团不可用');
	
	/**
	 * 格式错误
	 * @var array
	 */
	static $ERROR_FORMAT_ERROR = array('errno'=>101010, 'error'=>'[{p}]格式错误');
	
	/**
	 * 某某数据失败
	 * @var array
	 */
	static $ERROR_DATA_BAD = array('errno' => 101011, 'error' => '[{p}]数据失败');
	
	/**
	 * 没有设置手机支付账户
	 * @var array
	 */
	static $ERROR_NO_MOBILE_PAY = array('errno' => 101012, 'error' => '没有设置手机支付账户');
	
	/**
	 * 手机支付账户登录失败
	 * @var array
	 */
	static $ERROR_MOBILE_PAY_LOGIN_BAD = array('errno' => 101013, 'error' => '支付账户登录失败');
	
	/**
	 * 订单已支付
	 * @var array
	 */
	static $ERROR_MOBILE_PAY_ALREADY_PAY = array('errno' => 101014, 'error' => '订单已支付');
	
	/**
	 * 支付方式不存在
	 * @var array
	 */
	static $ERROR_NO_PAYMENT_CONFIG = array('errno' => 101015, 'error' => '支付方式不存在');
	
	/**
	 * 创建移动支付失败
	 * @var array
	 */
	static $ERROR_CREATE_MOBILE_PAY_BAD = array('errno' => 101016, 'error' => '创建移动支付失败');
	
	/**
	 * 查询移动支付失败
	 * @var array
	 */
	static $ERROR_TRADE_QUERY_BAD = array('errno' => 101017, 'error' => '查询移动支付失败');
	
	/**
	 * 移动支付金额错误
	 * @var array
	 */
	static $ERROR_MOBILE_PAY_MONEY_ERROR = array('errno' => 101018, 'error' => '移动支付金额错误');
	
	/**
	 * 自定义错误
	 */
	static $ERROR_NUMBER_BAD = array('errno' => 101019, 'error' => '{p}');
	
	
	
	
	
	
	
	
	
	
	
}
?>