<?php

/**
 *=================================================================== 
 * image.class.php 图片处理类，实现图片处理，包括添加水印和生成缩略图
 * @author 秦佳奇
 * @version 1.0
 * 2016年6月11日
 *===================================================================
 */

class Image{

	private $_thumbPrefix = 'thumb_'; //缩略图前缀
	private $_waterPrefix = 'watermark_'; //水印图前缀


	//创建画布资源函数集合，根据MIME类型选择
	private $_createCanvas = array(
		'image/gif'  => 'imageCreateFromGif',
		'image/png'  => 'imageCreateFromPng',
		'image/jpeg' => 'imageCreateFromJpeg'
		);

	//生成图像函数集合，根据MIME类型选择
	private $_generateImg = array(
		'image/gif'  => 'imageGif',
		'image/png'  => 'imagePng',
		'image/jpeg' => 'imageJpeg'
		);


	/**
	 * 添加水印功能
	 * @param $imageFile string 目标图片文件
	 * @param $waterFile string 水印图片文件
	 * @param $postion number 添加水印位置，默认9，右下角
	 * @param $path string 加水印后的图片存放路径,默认为空，表示在当前目录
	 * @return string 添加水印后的图片文件名
	 */

	public function waterMark($imageFile, $waterFile, $postion, $path){

		//获取原图和水印图片信息
		$src_info = getimagesize($imageFile);
		$warter_info = getimagesize($waterFile);
		//原图的宽高
		$src_w = $src_info[0];
		$src_h = $src_info[1];
		//水印图的宽高
		$warter_w = $warter_info[0];
		$warter_h = $warter_info[1];

		//判断文件类型创建函数名
		$srcCreateFrom = $this->_createCanvas[$src_info['mime']];
		$waterCreateFrom = $this->_createCanvas[$warter_info['mime']];
		//创建画布
		$srcImg = $srcCreateFrom($imageFile);
		$waterImg = $waterCreateFrom($waterFile);

		//水印位置选择
		switch ($postion) {
			//左上
			case 1:
				$dst_x = 0;
				$dst_y = 0;
				break;
			//中上
			case 2:
				$dst_x = ($src_w - $warter_w)/2;
				$dst_y = 0;
				break;
			//右上
			case 3:
				$dst_x = $src_w - $warter_w;
				$dst_y = 0;
				break;
			//中左
			case 4:
				$dst_x = 0;
				$dst_y = ($src_h - $warter_h)/2;
				break;
			//中中
			case 5:
				$dst_x = ($src_w - $warter_w)/2;
				$dst_y = ($src_h - $warter_h)/2;
				break;
			//中右
			case 6:
				$dst_x = $src_w - $warter_w;
				$dst_y = ($src_h - $warter_h)/2;
				break;
			//下左
			case 7:
				$dst_x = 0;
				$dst_y = $src_h - $warter_h;
				break;
			//下中
			case 8:
				$dst_x = ($src_w - $warter_w)/2;
				$dst_y = $src_h - $warter_h;
				break;
			//下右
			case 9:
				$dst_x = $src_w - $warter_w;
				$dst_y = $src_h - $warter_h;
				break;
			//随机
			case 0:
				$dst_x = rand(0,$src_w - $warter_w);
				$dst_y = rand(0,$src_h - $warter_h);
				break;
			default:
				break;
		}

		//添加水印图片到原图上
		imagecopy($srcImg, $waterImg, $dst_x, $dst_y, 0, 0, $warter_w, $warter_h);

		//生成带水印的图片
		$dstFile = $path . $this->_waterPrefix . basename($imageFile);
		$generateImage = $this->_generateImg[$src_info['mime']];

		if($generateImage($srcImg, $dstFile)){
			//图片生成成功，返回文件名
			return $dstFile;
		} else {
			return false;
		}

	}

	/**
	 * 生成缩略图,等比例缩放
	 * @param $imageFile string 目标图片文件,
	 * @param $max_width number 缩略图最大宽度
	 * @param $max_height number  缩略图最大高度
	 * @param $path string 加水印后的图片存放路径,默认为空，表示在当前目录
	 * @return string 成功返回缩略图名称，失败返回false
	 */
	public function thumbnail($imageFile,$max_width,$max_height,$path=''){
		//获取图片信息
		$src_info = getimagesize($imageFile);
		$src_w = $src_info[0];
		$src_h = $src_info[1];

		//通过计算比例，得到缩略图的大小
		if($src_w / $max_width  > $src_h / $max_height){
			// 此时应该以宽为准
			$dst_w = $max_width;
			$dst_h = ($max_width / $src_w) * $src_h;
		} else {
			// 此时应该以高为准
			$dst_h = $max_height;
			$dst_w = ($max_height / $src_h) * $src_w;
		}

		//创建原图资源
		$srcCreateFrom = $this->_createCanvas[$src_info['mime']];
		$srcImg = $srcCreateFrom($imageFile);
		//创建缩略图资源,以传进来的宽高为准统一，以防显示有问题
		$dstImg = imagecreatetruecolor($max_width, $max_height);
		//填充白色背景
		imagefill($dstImg, 0, 0, imagecolorallocate($dstImg, 255, 255, 255));

		//计算缩略图在画布上的位置,保证比例不等时图片能居中
		$dst_x = ($max_width - $dst_w)/2;
		$dst_y = ($max_height - $dst_h)/2;


		//按照比例生成缩略图:重采样拷贝部分图像并调整大小
		imagecopyresampled($dstImg, $srcImg, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		//也可以用basename函数
		$dstFile =  $path . $this->_thumbPrefix . basename($imageFile);

		$generateImage = $this->_generateImg[$src_info['mime']];
		if($generateImage($dstImg, $dstFile)){
			// 成功返回缩略图名称,注意返回的名称,不同地方上传方案会有不同的路径
			return $dstFile;
		} else {
			// 失败返回false
			return false;
		}
	}


}


