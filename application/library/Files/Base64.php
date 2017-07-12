<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		通过file表单形式上传文件处理类
 * @author	huihui
 * @date 2017.07.07
 * @version		1.0
 +-----------------------------------------
 */

class File_Base64{

	//mime和文件后缀对应关系
	private $mimeMap = array(
		'image/gif' => 'gif',
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/pjpeg' => 'jpg'
	);

	/**
	 * 解析上传数据
	 *
	 * @access public
	 * @param mixed $files	上传文件数据
	 * @return array		格式化后的标准数据
	 */
	public function dealFiles($files){
		$fileArray  = array();
		if(is_array($files)){
			foreach($files as $file){
				$fileArray[] = $this->parse($file);
			}
		}else{
			$fileArray[] = $this->parse($files);
		}

		return $fileArray;
	}

	/**
	 * 保存文件
	 *
	 * @access	public
	 * @param array $fileInfo 文件信息
	 * @param	string $saveName	存储文件位置
	 * @return boolean
	 */
	public function save($fileInfo, $saveName){
		if(@file_put_contents($saveName,$fileInfo['tmp_name'])){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 解析base64位图片信息
	 *
	 * @access private
	 * @param string $data		base64文件信息
	 * @return array		标准上传文件信息
	 */
	private function parse($data){
		$fileInfo = array('name'=>'');
		//解析文件类型
		$info = explode(';base64,',$data);
		$fileInfo['type'] = str_ireplace('data:', '', $info[0]);
		$fileInfo['ext'] = $this->mimeMap[$fileInfo['type']];
		$fileInfo['tmp_name'] = base64_decode(str_replace(' ', '+',$info[1]));
		$fileInfo['size'] = strlen($fileInfo['tmp_name']);
		$fileInfo['error'] = 0;
		return $fileInfo;
	}
}