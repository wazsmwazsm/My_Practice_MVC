<?php

/**
 *=================================================================== 
 * Controller.class.php 基础控制器类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class Controller{
	
	/*
	 * function : 构造函数
	 */
	public function __construct(){
		$this->__initContentType();
	}


	/*
	 * function : 初始化content-type
	 */
	protected function __initContentType(){
		header('Content-Type:text/html;charset=utf-8');
	}

	/*
	 * function : 跳转
	 * @param : $url string 要跳转的路径
	 * @param : $message string 跳转提示信息
	 * @param : $wait int 等待时间
	 */
	protected function _jump($url, $message, $wait = 3){
		if(is_null($message)){
			//如果立即跳转
			header('Location: '. $url);
		}else{
			//等待跳转，显示跳转消息
			header('Refresh: $wait; URL=$url');
			echo $info;
		}
		//防止后续执行
		die;
	} 



	/*
	 * function : 载入辅助函数，由用户决定手动加载时机和位置
	 */
	protected function _helper($helper){
		require HELPER_PATH . '{$helper}_helper.php';
	}

	/*
	 * function : 载入库类，由用户决定手动加载时机和位置
	 */
	protected function _libaray($lib){
		require LIB_PATH . '{$lib}.class.php';
	}


}