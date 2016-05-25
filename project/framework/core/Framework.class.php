<?php

/**
 *=================================================================== 
 * Framework.class.php 核心启动类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class Framework {

	/*
	 * function : 启动
	 */
	public static function run(){

		//初始化路径
		self::_initPath();
		//初始化配置文件
		self::_initConfig();
		//注册自动加载
		self::_autoload();
		//初始化参数
		self::_init();		
		//开始路由(处理分发请求)
		self::_router();
	}

	/*
	 * function : 初始化配置文件
	 */
	private static function _initConfig(){
		$GLOBALS['config'] = require CONFIG_PATH . 'config.php';
	}

	
	/*
	 * function : 初始化路径
	 */
	private static function _initPath(){
		//定义系统目录路径
		define('DS',DIRECTORY_SEPARATOR);
		define('ROOT', getcwd() . DS); //根目录
		define('APP_PATH', ROOT . 'application' . DS);
		define('FRAMEWORK_PATH', ROOT . 'framework' . DS);
		define('PUBLIC_PATH', ROOT . 'public' . DS);
		define('MODEL_PATH', APP_PATH . 'models' . DS);
		define('CONTROLLER_PATH', APP_PATH . 'controllers' . DS);
		define('VIEW_PATH', APP_PATH . 'views' . DS);
		define('CONFIG_PATH', APP_PATH . 'config' . DS);
		define('CORE_PATH', FRAMEWORK_PATH . 'core' . DS);
		define('DB_PATH', FRAMEWORK_PATH . 'database' . DS);
		define('HELPER_PATH', FRAMEWORK_PATH . 'helpers' . DS);
		define('LIB_PATH', FRAMEWORK_PATH . 'libraries' . DS);
	}


	/*
	 * function : 初始化分发参数、加载核心类
	 */
	private static function _init(){	
		//确定分发参数
		define('PLATFORM', isset($_REQUEST['p']) ? $_REQUEST['p'] : $GLOBALS['config']['app']['default_platform']);
		define('CONTROLLER', isset($_REQUEST['c']) ? ucfirst($_REQUEST['c']) : $GLOBALS['config'][PLATFORM]['default_controller']); //ucfirst将首字母大写
		define('ACTION', isset($_REQUEST['a']) ? $_REQUEST['a'] : $GLOBALS['config'][PLATFORM]['default_action']);

		//当前控制器动作目录
		define('CUR_CONTROLLER_PATH', CONTROLLER_PATH . PLATFORM . DS);
		define('CUR_VIEW_PATH', VIEW_PATH . PLATFORM . DS);


		//手动加载核心类
		//必须加载的核心类列表
		$frameworkClassList = array(
			//'类名'' => '类文件地址'
			'Factory'	    => 	CORE_PATH . 'Factory.class.php',			
			'I_DAO' 		=> 	DB_PATH . 'I_DAO.interface.php',
			'MysqlDB'	    => 	DB_PATH . 'MySQLDB.class.php',
			'PDODB'		    => 	DB_PATH . 'PDODB.class.php',
			'Model' 		=> 	CORE_PATH . 'Model.class.php',
			'Controller' 	=> 	CORE_PATH . 'Controller.class.php',
			);
		//载入数据库类
		foreach ($frameworkClassList as $value) {
			require $value;
		}
	}

	/*
	 * function : 进行分发处理
	 */
	private static function _router(){
		//确定类名和方法名
		$controllerName = CONTROLLER . 'Controller';
		$actionName = ACTION . 'Action';

		//实例化控制器，调用相应的方法
		$controller = new $controllerName;
		$controller->$actionName();

	}

	/*
	 * function : 注册自动加载方法
	 */
	private static function _autoload(){
		//__CLASS__是字符串，self是类本身,对象可以用$this
		spl_autoload_register(array(__CLASS__ , 'userAutoload'));

	}

	/*
	 * function : 自定义自动加载方法
	 * @param : $className 类名
	 */
	public static function userAutoload($className){

		//只负责加载application下的控制器类和模型类
		if(substr($className, -10) == 'Controller'){
			require CUR_CONTROLLER_PATH . $className . '.class.php';
		} elseif(substr($className, -5) == 'Model'){
			require MODEL_PATH . $className . '.class.php';
		}

	}

}




