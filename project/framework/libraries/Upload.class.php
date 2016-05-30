<?php

/**
 *================================================================== 
 * upload.class.php 文件上传类，实现文件上传功能
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月30日
 *==================================================================
 */
class Upload {

	//可配置的属性
	private $_maxSize;
	private $_typeMap;
	private $_allowExtList;
	private $_allowMimeList;
	private $_uploadPath;
	private $_prefix;

	//错误信息
	private $_error;


	/**
	 * 构造方法
	 * @param $type string 上传文件类型(品牌的还是商品的)
	 * @param $path string 上传的路径
	 */
	public function __construct($type, $path = UPLOAD_PATH){
		//上传目录
		$this->_uploadPath = UPLOAD_PATH . $type . DS;
		

		//文件前缀
		$this->_prefix = $type . "_";
		
		//上传最大尺寸
		$this->_maxSize = 1024*1024*2;

		//设置一个后缀名与mine的映射关系(可能更多，这里只处理图片)
		$this->_typeMap = array(
			'.png' => array('image/png','image/x-png'),
			'.jpg' => array('image/jpeg','image/pjpeg'),
			'.jpeg' => array('image/jpeg','image/pjpeg'),
			'.gif' => array('image/gif'),
			);
		//允许上传的后缀名
		$this->_allowExtList = array('.png','.gif','.jpg','.jpeg');

		//用后缀名计算允许上传的MIME类型
		$allow_mime_list = array();

		foreach ($this->_allowExtList as $value) {
			$allow_mime_list = array_merge($allow_mime_list,$this->_typeMap[$value]);
		}

		//去重复
		$this->_allowMimeList = array_unique($allow_mime_list);


	}

	
	public function __set($property, $value){
		$allow_set_list = array('_allowExtList','_maxSize');

		//没有_的自动添加
		if(substr($property, 0, 1) !== '_'){
			$property = '_' . $property;
		}

		if(!in_array($property, $allow_set_list)){
			//访问不允许设置的参数
			return false;
		}

		//允许访问，设置
		$this->$property = $value;

	}



	/**
	 * 单文件上传方法
	 * @param $tmp_file array 包含上传文件信息的数组
	 * @return string 成功返回上传的文件名(子目录名+文件名)
	 */
	public function uploadOne($tmp_file){

		//是否存在错误
		if($tmp_file['error'] != 0){
			$this->_error = '文件上传错误';
			return false;
		}

		//文件大小
		if($tmp_file['size'] > $this->_maxSize){
			$this->_error = '文件过大';
			return false;
		}	

		//判断扩展名
		//将大写扩展名转换为小写
		$ext = strtolower(strrchr($tmp_file['name'], '.'));

		if(!in_array($ext, $this->_allowExtList)){
			$this->_error = "类型不合法";
			return false;
		}

		//判断MIME类型
		if(!in_array($tmp_file['type'], $this->_allowMimeList)){
			$this->_error = 'MIME类型不合法';
			return false;
		}

		//PHP自己获取文件的mime类型去检测,比浏览器提供的更加安全可靠
		//获得的绝对真实的文件类型
		$finfo = new finfo(FILEINFO_MIME_TYPE);//获取一个可以检测文件信息的对象

		$mime_type = $finfo->file($tmp_file['tmp_name']);
		if(!in_array($mime_type, $this->_allowMimeList)){
			$this->_error = 'MIME类型不合法';
			return false;
		}


		//移动暂存文件到上传目录

		//创建子目录
		$sub_dir = date('YmdH') . '/';
		if(!is_dir($this->_uploadPath . $sub_dir)){
			//不存在目录，创建
			mkdir($this->_uploadPath . $sub_dir);
		}
		
		//构建唯一的文件名
		$upload_filename = uniqid($this->_prefix, true) . $ext;

		//移动
		if(move_uploaded_file($tmp_file['tmp_name'], $this->_uploadPath . $sub_dir . $upload_filename)){
			//移动成功
			return $sub_dir . $upload_filename;
		} else {
			//移动失败
			$this->_error = '移动失败';
			return false;
		}

	}

	/**
	 * 多文件上传方法
	 * @param $tmp_files array 包含上传文件信息的数组，是一个二维数组
	 * @return array 成功返回上传的文件名构成的数组, 如果有失败的则不太好处理了
	 */
	public function uploadAll($tmp_files){

		//放置所有上传文件名称的数组
		$files = array();
		foreach ($tmp_files['error'] as $key => $value) {
			$tmp_file = array();
			$tmp_file['name'] = $tmp_files['name'][$key];
			$tmp_file['type'] = $tmp_files['type'][$key];
			$tmp_file['tmp_name'] = $tmp_files['tmp_name'][$key];
			$tmp_file['error'] = $tmp_files['error'][$key];
			$tmp_file['size'] = $tmp_files['size'][$key];

			//上传
			$files[] = $this->uploadOne($tmp_file);
		}
		//返回上传名称数组
		return $files;
	}

	//获得错误信息
	public function getError(){
		return $this->_error;
	}
}




