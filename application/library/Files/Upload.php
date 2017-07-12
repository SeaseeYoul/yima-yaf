<?php
/**
 +-----------------------------------------
 * Facile PHP System Framework
 +-----------------------------------------
 * @description		通过file表单形式上传文件处理类
 * @author	灰灰
 * @date 2017年07月12日
 * @version		1.0
 +-----------------------------------------
 */

class Files_Upload{


	/**
	 * 解析上传数据
	 *
	 * @access public
	 * @param mixed $files	上传文件数据
	 * @return array		格式化后的标准数据
	 */
	public function dealFiles($files){
		$fileArray  = array();
		$n = 0;
		foreach ($files as $k=>$file){
			if(is_array($file['name'])){
				$keys = array_keys($file);
				foreach($file['name'] as $key=>$name){

					foreach($keys as $_key){
						$fileArray[$n][$_key] = $file[$_key][$key];
					}
					$fileArray[$n]['ext'] = pathinfo($name,PATHINFO_EXTENSION);
					++$n;
				}
			}else{
				$fileArray[$n] = $file;
				$fileArray[$n]['ext'] = pathinfo($file['name'],PATHINFO_EXTENSION);
			}
			++$n;
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
		if(@move_uploaded_file($fileInfo['tmp_name'],$saveName)){
			return true;
		}else{
			return false;
		}
	}

}