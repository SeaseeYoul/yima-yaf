<?php
/**
 * @description		静态资源管理服务
 * @author	灰灰
 * @date 2017年07月11日
 +-----------------------------------------
 */

class Files_ImageUpload {

	// 资源存储规则
	protected $rule;

	// 资源缓存池
	protected $tmpFiles;

	// 错误信息
	protected $error;

	// 计算后的图片存储信息
	protected $saveInfo;

	/**
	 * 构造方法，初始化资源存储规则
	 *
	 * @access public
	 * @param string $rule		资源存储规则标识
	 * @param array $config		资源存储全部配置
	 * @return void
	 */
	public function __construct($rule, $config = array()){

		// 采用继承式配置
		$config || $config = Config::$config['RESOURCE_SAVE_RULE'];
		$this->rule = $config['RULE'][$rule];
		isset($this->rule['TMP_PATH']) || $this->rule['TMP_PATH'] = $config['TMP_PATH'];
		isset($this->rule['ROOT_PATH']) || $this->rule['ROOT_PATH'] = $config['ROOT_PATH'];
		isset($this->rule['IMG_QUALITY']) || $this->rule['IMG_QUALITY'] = $config['IMG_QUALITY'];
		isset($this->rule['SAVE_TYPE']) || $this->rule['SAVE_TYPE'] = $config['SAVE_TYPE'];
		isset($this->rule['GRADE']) || $this->rule['GRADE'] = $config['GRADE'];
		isset($this->rule['FILE_TYPE']) || $this->rule['FILE_TYPE'] = $config['FILE_TYPE'];
		isset($this->rule['CDN']) || $this->rule['CDN'] = $config['CDN'];
		isset($this->rule['BASE64_IDENTIFY']) || $this->rule['BASE64_IDENTIFY'] = $config['BASE64_IDENTIFY'];
		isset($this->rule['CLIP_RULE']) || $this->rule['CLIP_RULE'] = $config['CLIP_RULE'];
	}

	/**
	 * 获取特定资源处理类实例
	 *
	 * @static
	 * @access public
	 * @param string $rule		规则名称
	 * @param array $config		资源存储全部配置
	 * @return object
	 */
	public static function getInstance($rule, $config = array()){
		return new self($rule, $config);
	}

	/**
	 * 上传资源，自动识别上传信息
	 *
	 * @access public
	 * @return boolean
	 */
	public function upload(){
	    
		$upload = new Core_Upload(array(
			'savePath' => $this->rule['TMP_PATH'],
			'fileType' => $this->rule['FILE_TYPE'],
			'base64Identify' => $this->rule['BASE64_IDENTIFY']
		));

		$this->tmpFiles = $upload->upload();
		if($this->tmpFiles){
			return true;
		}else{
			$this->error = $upload->getError();
			return false;
		}
	}

	/**
	 * 获取上传错误信息
	 *
	 * @access public
	 * @return array
	 */
	public function getUpError(){
		return array('errcode' => 1020100 + $this->error[0], 'message'=> $this->error[1]);
	}

	/**
	 * 获取资源存储规信息
	 * 解析配置的对应存储规则，计算资源存储信息
	 *
	 * @access public
	 * @param string $identifying		存储文件的唯一标识，一般为用户id
	 * @param boole  $cdn				是否返回带cdn的文件地址
	 * @param	array $files			临时文件存储信息
	 * @return array					返回存储文件信息
	 */
	public function getFiles($identifying, $cdn=false, $files=array()){
		$files || $files = $this->tmpFiles;
		$this->saveInfo = array();

		//获取图片中的随机数及数量
		preg_match('/(X{3,})/', $this->rule['SAVE_RULE'],$random);
		$count = strlen($random[1]);
		$cdn = $cdn ? $this->rule['CDN'] : '';

		//生成子目录替换规则
		$subdirectory = ceil($identifying/$this->rule['GRADE']);
		$year = date('Y');
		$week = date('W');
		$rule = array('{subdirectory}','{identification}',$random[1],'{year}','{week}');

		//根据资源规则生成图片储存路径
		foreach($files as $file){

			// 原始资源存储信息
			$pic = str_replace($rule,array($subdirectory,$identifying,$this->randStr($count),$year,$week),$this->rule['SAVE_RULE'].'.'.pathinfo($file,PATHINFO_EXTENSION));
			$pathParts = pathinfo($pic);
			$fileType = $this->rule['SAVE_TYPE'] ? '.'.$pathParts['extension'] : $this->rule['SAVE_TYPE'];

			//生成缩略图信息
			$thumbs = $thumbInfo = $thumbSize = array();
			if(isset($this->rule['CROP']) && $this->rule['CROP']){
				foreach($this->rule['SIZE'] as $prefix => $size){
					$thumbInfo[$prefix] = Core_Image::thumb($file, $size[0], $size[1], $this->rule['CLIP_RULE']);
					$thumbSize[$prefix] = array(
						'width' => (string)round($thumbInfo[$prefix][0],2),
						'height' => (string)round($thumbInfo[$prefix][1],2)
					);
				}
			}

			// 资源存储信息
			$this->saveInfo[] = array(
				'tmp_file' => $file,
				'pic' => $cdn.$pic,
				'size' => $thumbSize
			);
		}
		return $this->saveInfo;
	}

	/**
	 * 按规则保存资源
	 * @access public
	 * @param string $files		通过getFiles()方法计算的临时文件和原文件对应关系
	 * @param string $retainTmpFile	是否保留原图
	 * @return boole 			返回是否都处理成功
	 */
	public function save($files = array(), $retainTmpFile=false){

		$files || $files = $this->saveInfo;
		$fun = $retainTmpFile ? 'copy' : 'rename';


		foreach($files as $key => $file){
			$fileInfo = $this->rule['ROOT_PATH'].str_ireplace($this->rule['CDN'],'',$file['pic']);

			// 创建父目录
			$dirname = dirname($fileInfo);
			if(!is_dir($dirname)){
				mkdir($dirname, 0777, true);
			}

			// 处理原图信息
			try{
				$fun($file['tmp_file'], $fileInfo);
			}catch (Exception $e){
				Log::record(array('type'=>"moveFile",'fun'=>$fun,'file'=>$file['tmp_file'],'new_file'=>$fileInfo));
			}
		}
		return true;
	}

	/**
	 * 根据原图地址获取指定标识的图像地址
	 *
	 * @access public
	 * @param string $file	原图地址
	 * @param string $type	一般是根据checkSize()方法获取的标识
	 * @param boole $cdn  	是否需要包含cnd信息,需要自己确认图片是否已经是网络图片
	 * @return string 对应的图片地址
	 */
	public function getTypeFile($file,$type=null,$cdn=false,$crop = 2){

		$cdn = $cdn ? $this->rule['CDN'] : '';
		$type = strtoupper($type);

		// 返回默认图片
		if(empty($file)){
			if(!isset($this->rule['DEFAULT'])){
				return '';
			}
			$file = $this->rule['DEFAULT'];
		}

		// 返回缩略图
		if(!empty($type)){
			return $cdn.$file."_{$this->rule['SIZE'][$type][0]}_{$this->rule['SIZE'][$type][1]}_{$crop}_{$this->rule['IMG_QUALITY']}{$this->rule['SAVE_TYPE']}";
		}

		// 返回资源原始地址
		return $cdn.str_replace($cdn, '', $file);
	}

	/**
	 * 根据原图地址删除所有缩略图
	 * @access public
	 * @param string file
	 * @return void
	 */
	public function delPrimeval($file){
		$file = str_replace($this->rule['CDN'],'',$file);
		if(empty($file)){
			return false;
		}
		$file = $this->rule['ROOT_PATH'].$file;
		if(file_exists($file)){
			unlink($file) || Log::record(array('type'=>'UNLINK','file'=>$file));
		}
	}

	/**
	 * 产生一个指定长度的随机字符串
	 *
	 * @access public
	 * @param int $len 产生字符串的位数
	 * @return string
	 */
	protected function randStr($length=6){
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}

	/**
	 * 获取指定大小的图片尺寸
	 *
	 * @access public
	 * @param array $thumbSzie	所有缩略图尺寸
	 * @param string $type 要获取的缩略图尺寸
	 * @return array 缩略图尺寸信息
	 */
	public function getSize($thumbSzie, $type){
		if($thumbSzie[$type]){
			$size = $thumbSzie[$type];
			return array('width'=>(string)round($size['width']),'height'=>(string)round($size['height']));
		}else{
			$size = $this->rule['size'][$type];
			return array('width'=>(string)$size[0],'height'=>(string)$size[1]);
		}
	}
}