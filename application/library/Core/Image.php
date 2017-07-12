<?php
/**
 +-----------------------------------------
 * @description		图像处理
 * @author	huihui
 * @date 2017年07月12日
 * @version	 1.0
 +-----------------------------------------
 */

class Core_Image {

	// 缩略图相关常量定义
	const IMAGE_THUMB_SCALE     =   1 ; //常量，标识缩略图等比例缩放类型
	const IMAGE_THUMB_CENTER    =   2 ; //常量，标识缩略图居中裁剪类型
	const IMAGE_THUMB_FIXED     =   0 ; //常量，标识缩略图固定尺寸缩放类型

	// 图片资源
	private $img;

	/**
	 * 生成缩略图
	 *
	 * @access public
	 * @param	string $file	图片文件
	 * @param  integer $width  缩略图最大宽度
	 * @param  integer $height 缩略图最大高度
	 * @param  integer $type   缩略图裁剪类型
	 * @return Object          当前图片处理库对象
	 */
	public static function thumb($file,$width, $height, $type = self::IMAGE_THUMB_SCALE){

		// 获取图片信息
		list($w, $h) = getimagesize($file);
		switch ($type) {

			// 等比缩放
			case Core_Image::IMAGE_THUMB_SCALE: 
				$x = $y = 0;

				//原图尺寸小于缩略图尺寸则不进行缩略
				if($w < $width && $h < $height){
					$width = $w;
					$height = $h;
					break;
				}

				//计算缩放比例
				$scale = min($width/$w, $height/$h);
				$width  = $w * $scale;
				$height = $h * $scale;
				break;

			// 居中裁剪
			case Core_Image::IMAGE_THUMB_CENTER:
				if($width > $w || $height > $h){
					$scale = max($width/$w, $height/$h);
					$width = $width/$scale;
					$height = $height/$scale;
				}

				//计算缩放比例
				$scale = max($width/$w, $height/$h);

				//设置缩略图的坐标及宽度和高度
				$ow = $w;
				$oh = $h;
				$w = $width/$scale;
				$h = $height/$scale;
				$x = ($ow - $w)/2;
				$y = ($oh - $h)/2;
				break;

			// 固定
			default:
				$x = $y = 0;
				$width = $w;
				$height = $h;
				break;
		}

		return array($width, $height, $w, $h, $x, $y);
	}

	/**
	 * 打开一张图像
	 * @param  string $imgname 图像路径
	 */
	public function open($imgname){

		//获取图像信息
		$info = getimagesize($imgname);

		//销毁已存在的图像
		empty($this->img) || imagedestroy($this->img);

		//设置图像信息
		$this->info = array(
			'width'  => $info[0],
			'height' => $info[1],
			'type'   => image_type_to_extension($info[2], false),
			'mime'   => $info['mime'],
		);

		//打开图像
		$fun = "imagecreatefrom{$this->info['type']}";
		$this->img = $fun($imgname);
	}

	/**
	 * 裁剪图像
	 * @param  integer $w      裁剪区域宽度
	 * @param  integer $h      裁剪区域高度
	 * @param  integer $x      裁剪区域x坐标
	 * @param  integer $y      裁剪区域y坐标
	 * @param  integer $width  图像保存宽度
	 * @param  integer $height 图像保存高度
	 */
	public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null){

		//设置保存尺寸
		empty($width)  && $width  = $w;
		empty($height) && $height = $h;

		//创建新图像
		$img = imagecreatetruecolor($width, $height);
		// 调整默认颜色
		$color = imagecolorallocate($img, 255, 255, 255);
		imagefill($img, 0, 0, $color);

		//裁剪
		imagecopyresampled($img, $this->img, 0, 0, $x, $y, $width, $height, $w, $h);
		imagedestroy($this->img); //销毁原图

		//设置新图像
		$this->img = $img;
	}

	/**
	 * 保存图像
	 *
	 * @param  string  $imgname   图像保存名称
	 * @param  string  $ratio     jpeg图像压缩比率
	 * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
	 */
	public function save($imgname, $ratio = 100, $interlace = true){

		// 自动获取图像类型
		$type = strtolower(pathinfo($imgname, PATHINFO_EXTENSION));

	 	//JPEG图像设置隔行扫描
        if('jpeg' == $type || 'jpg' == $type){
            $type = 'jpeg';
            imageinterlace($this->img, $interlace);

            // 按指定压缩比保存图像
            return imagejpeg($this->img, $imgname, $ratio);
        }

		//保存图像
		$fun = "image{$this->info['type']}";
		return $fun($this->img, $imgname);
	}
}