<?php

/**
 *=================================================================== 
 * Captcha.class.php 验证码工具类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */
class Captcha {


	private $_captchaCode; //验证码字符串
	private $_codeLen;		//验证码长度
	private	$_imgWidth;		//画布宽度
	private	$_imgHeight;		//画布高度
	private $_fontSize;		//字体大小
	private $_fontStyle;	//字体样式
	private $_bgColor;		//背景颜色
	private $_fontColor;	//字体颜色
	private $_img;			//图片资源
	
	/*
	 *function : 构造函数，初始化变量
	 * @param : $codeLen int 验证码长度
	 * @param : $width int 画布宽度
	 * @param : $height int 画布长度
	 * @param : $fontColor string 字体颜色
	 */
	public function __construct($codeLen=4, $width=145, $height=45, $fontColor = 'white'){
		//验证码长度
		$this->_codeLen = $codeLen;
		//画布宽
		$this->_imgWidth = $width;
		//画布高
		$this->_imgHeight = $height;

		//创建画布
		$this->_img = imagecreate($this->_imgWidth, $this->_imgHeight);

		//字体颜色库
		$fontColorList = array(
			'white' => imagecolorallocate($this->_img, 255, 255, 255),
			'black' => imagecolorallocate($this->_img, 0, 0, 0),
			'red' => imagecolorallocate($this->_img, 255, 0, 0),
			'blue' => imagecolorallocate($this->_img, 0, 0, 255),
			'green' => imagecolorallocate($this->_img, 0, 255, 0),
			);
		//字体颜色默认白色
		$this->_fontColor = $fontColorList[$fontColor];

		//字体大小根据验证码长度计算
		$this->_fontSize = 100 / $this->_codeLen;

		//字体获取
		switch(PHP_OS)
		{
			case 'Windows':
				define('FONT','C://Windows/fonts/Arial.ttf');
				break;
			case 'Linux':
				define('FONT','/usr/share/fonts/truetype/ubuntu-font-family/Ubuntu-BI.ttf');
				break;	
			default:
				define('FONT','./elephant.ttf');
				break;				
		} 

		$this->_fontStyle = FONT;

	}


	/*
	 *function : 生成背景
	 */
	private function _createBg(){
		
		//创建一个随机的背景颜色，画布填充颜色
		$this->_bgColor = imagecolorallocate($this->_img, rand(50,200), rand(0,155), rand(0,155));
		//填充颜色
		imagefilledrectangle($this->_img, 0, $this->_imgHeight, $this->_imgWidth, 0, $this->_bgColor);

	}
		

	/*
	 *function : 产生随机验证码
	 */
	private function _createCode(){
		for ($i=0; $i < $this->_codeLen ; $i++) { 
			// 48-57对应ascii表中的0~9,65-90对应大写字母,97-122为小写字母
			$randAscciiNumArray = array(rand(48,57),rand(65,90),rand(97,122));
			// 随机进行扫码，可能是数字、大写字母、小写字母
			$randAsciiNum = $randAscciiNumArray[rand(0,2)];
			// chr函数将ascii码转换为字符串
			$randStr = chr($randAsciiNum);
			
			//构建验证码字符串
			$this->_captchaCode .= $randStr;
		}

		//开启session，防止重复开启报错
		@ session_start();

		//写入session变量
		$_SESSION['captcha_code'] = $this->_captchaCode;

	}

	/*
	 *function : 将字体打印在画布上
	 */
	private function _printFont(){

		//将随机产生的字符写入到画布,进行一些位置调整
		for($i=0; $i < $this->_codeLen ; $i++){
			$angle = rand(0,20) - rand(0,25); //倾斜角度
			$font_x = 10+$i*(100/$this->_codeLen + 30/$this->_codeLen);
			$font_y = rand(100/$this->_codeLen + 2*$this->_codeLen,100/$this->_codeLen + 2*$this->_codeLen + 5);
			imagettftext($this->_img, $this->_fontSize, $angle, $font_x, $font_y, $this->_fontColor, $this->_fontStyle, $this->_captchaCode[$i]);
		}

	}		

	/*
	 *function : 产生干扰线
	 */
	private function _createLine(){
		$line_num = 8;
		for ($i=0; $i < $line_num; $i++) { 
			$lineColor = imagecolorallocate($this->_img, rand(0,255), rand(0,255), rand(0,255));

			$line_x1 = rand(0,$this->_imgWidth);
			$line_x2 = 0;
			$line_y1 = rand(0,$this->_imgWidth);
			$line_y2 = $this->_imgHeight;
			imageline($this->_img, $line_x1, $line_x2, $line_y1, $line_y2, $lineColor);
		}
	}

	/*
	 *function : 产生干扰点
	 */
	private function _createDot(){
		$pixel_num = 250;
		for ($i=0; $i < $pixel_num; $i++) { 
			$pixel_x = rand(0,$this->_imgWidth);
			$pixel_y = rand(0,$this->_imgHeight);
			imagesetpixel($this->_img, $pixel_x, $pixel_y, $this->_fontColor);
		}
	}

	/*
	 *function : 输出验证码
	 */
	private function _putPng(){

		header('Content-type:image/png;');
		imagepng($this->_img);

		imagedestroy($this->_img);
	}
	
	/*
	 *function : 外部获取生成验证码
	 */
	public function generateCode(){
		$this->_createBg();
		$this->_createCode();
		$this->_printFont();
		$this->_createLine();
		$this->_createDot();
		$this->_putPng();
	}


	/*
	 *function :获取验证码
	 */
	public function getCaptcha(){
		return $this->_captchaCode;
	}


	/* function: 验证
	 * @param $request_code 用户表单中提交的验证码
	 * @return bool
	 */

	public function checkCaptcha($request_code){

		@session_start();

		//存在且相等(strcasecmp不区分大小写)
		$result = isset($request_code) && isset($_SESSION['captcha_code']) && strcasecmp($request_code, $_SESSION['captcha_code']) == 0;

		//销毁session中的验证码值,防止被人拿去进行旧的session验证码匹配而忽略新生成的验证码
		//保证一时刻验证码是唯一的值
		unset($_SESSION['captcha_code']);
		
		return $result;
	}

}