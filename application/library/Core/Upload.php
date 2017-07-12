<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		文件上传统一接口
 * @author	灰灰
 * @date 2017年07月12日
 * @version		1.0
 +-----------------------------------------
 */

class Core_Upload {

	// 上传配置
	private $config = array(
		'fileType'          =>  array(), //允许上传的文件类型
		'savePath'      =>  '', //保存路径
		'base64Identify'=> 'base64File'	//base64位图片上传标识
	);

	// 被上传的数据
	private $data;

	// 上传驱动对象
	private $uploader;

	// 上传错误信息
	private $errCode = 0;

	//错误信息
	private $error = array(
		0 => '文件上传成功',
		1 => '文件大小超过限制',
		2 => '文件总大小超过限制',
		3 => '文件只有部分被上传',
		4 => '没有文件被上传',
		5 => '文件上传失败',
		6 => '文件上传失败',
		7 => '文件上传失败',
		8 => '文件类型不被允许',
		9 => '服务器故障，请稍后再试！'
	);

	/**
	 * 构造方法，初始化配置信息
	 *
	 * @access public
	 * @param array $config		配置信息
	 * @return void
	 */
	public function __construct($config){

		// 获取配置
		$this->config = array_merge($this->config, $config);

		// 根据上传数据自动识别上传驱动
		if(isset($_FILES) && !empty($_FILES)){
			$this->data = $_FILES;       
			$this->uploader = new Files_Upload(); 

		}else if(isset($_POST['base64File']) && !empty($_POST['base64File'])){
			$this->data = $_POST['base64File'];
			$this->uploader = new File_Base64();

		}else{
			$this->errCode = 4;
		}
	}

	/**
	 * 文件上传
	 *
	 * @access public
	 * @return mixed
	 */
	public function upload(){

		// 检测是否存在错误信息
		if($this->errCode > 0){
			return false;
		}

		//检查上传目录，不存在则尝试创建
		if(!$this->createDir($this->config['savePath'])){
			$this->errCode = 9;
			return false;
		}

		//解析上传数据信息
		$files = $this->uploader->dealFiles($this->data);

		$upFiles = array();
		foreach($files as $key => $file){

			//判断文件错误信息
			if($file['error'] > 0){
				$this->errCode = $file['error'];
				return false;
			}

			//检查文件是否完整
			if($file['size'] < 10){
				$this->errCode = 3;
				return false;
			}

			//检查文件类型是否被允许
			$file['ext'] = strtolower($file['ext']);
			if(!empty($this->config['fileType']) && !in_array($file['ext'], $this->config['fileType'])){
				$this->errCode = 8;
				return false;
			}

			/* 对图像文件进行严格检测 */
			if(in_array($file['ext'], array('gif','jpg','jpeg','bmp','png','swf')) && is_file($file['tmp_name'])) {
				$imginfo = getimagesize($file['tmp_name']);
				if(empty($imginfo) || ($file['ext'] == 'gif' && empty($imginfo['bits']))){
					$this->errCode = 8;
					return false;
				}
			}

			//保存文件
			$saveName = $this->getSaveName($file['ext']);
			if(!$this->uploader->save($file, $saveName)){
				$this->errCode = 4;
				return false;
			}
			$upFiles[] = $saveName;
		}
		return $upFiles ? $upFiles : false;
	}

	/**
	 * 获取错误信息
	 *
	 * @access public
	 * @return array
	 */
	public function getError(){
		return array(
			0 => $this->errCode,
			1 => $this->error[$this->errCode],
			'errcode' => $this->errCode,
			'message' => $this->error[$this->errCode]
		);
	}

	/**
	 * 创建目录
	 *
	 * @access private
	 * @param string $dir	目录名称或路径
	 * @return boolean
	 */
	private function createDir($dir){

		if(!is_dir($dir)){
			return @mkdir($dir,0777,true);
		}
		return true;
	}

	/**
	 * 生成保存文件名
	 *
	 * @access private
	 * @param string $ext	文件后缀
	 * @return string
	 */
	private function getSaveName($ext){

		do{
			$fileName = rtrim($this->config['savePath'],'/').'/'.uniqid(mt_rand(0,999999),true).'.'.$ext;
		}while(is_file($fileName));

		return $fileName;
	}
}