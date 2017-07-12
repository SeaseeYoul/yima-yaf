<?php

$config = array();

/**
 * 资源存储规则相关配置
 *
 *	TMP_PATH					临时目录
 *	ROOT_PATH					存储资源根目录
 *	CDN							资源访问加速地址
 *	IMG_QUALITY					图像处理后存储质量
 *	GRADE						子目录存储因子
 *	SAVE_TYPE					图片处理后存储格式
 *	FILE_TYPE					允许上传文件的类型
 *	BASE64_IDENTIFY				base64位上传资源采用post下标名称
 * 	CLIP_RULE					裁图规则
 * 					0		固定尺寸缩放类型
 * 					1		等比例缩放类型
 * 					2		居中裁剪类型
 *	RULE						制定不同类型资源的存储规则
 */

$config['RESOURCE_SAVE_RULE']['TMP_PATH'] = PUBLIC_PATH.'tmp/';
$config['RESOURCE_SAVE_RULE']['ROOT_PATH'] = PUBLIC_PATH.'Uploads/';
$config['RESOURCE_SAVE_RULE']['CDN'] = '';
$config['RESOURCE_SAVE_RULE']['IMG_QUALITY'] = 70;
$config['RESOURCE_SAVE_RULE']['GRADE'] = 10000;
$config['RESOURCE_SAVE_RULE']['SAVE_TYPE'] = '.jpg';
$config['RESOURCE_SAVE_RULE']['FILE_TYPE'] = array('gif', 'jpg', 'png', 'jpeg');
$config['RESOURCE_SAVE_RULE']['BASE64_IDENTIFY'] = 'base64File';
$config['RESOURCE_SAVE_RULE']['CLIP_RULE'] = 2;
$config['RESOURCE_SAVE_RULE']['RULE'] = array(
    'Image' => array(
        'IMG_QUALITY' => 80,
        'SAVE_RULE' => 'image/{subdirectory}/{identification}_XXXXXX',
        'SIZE' => array('S'=>array(100,100),'M'=>array(200,200),'B'=>array(300,300),'L'=>array(400,400),'G'=>array(600,600)),
        'DEFAULT' => 'image/0/0_XXXXXX.png',
        'CROP' => true,
    ),
);

return $config;
?>