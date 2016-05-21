<?php

/**
 *=================================================================== 
 * SessionDB.class.php session入库工具类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */
class SessionDB{

	//DAO对象
	private $_dao;
	//项目表前缀
	private $_prefix;

	public function __construct(){ 
		//初始化前缀
		$this->_prefix = $GLOBALS['config']['app']['table_prefix'];
		//设置session处理器
		ini_set('session.save_handler','user');
		//传入方法参数的语法,传入一个数组，指定所属的对象
		session_set_save_handler(
			array($this,'userSessionBegin'),
			array($this,'usersessionend'),
			array($this,'userSessionRead'),
			array($this,'userSessionWrite'),
			array($this,'userSessionDelete'),
			array($this,'userSessionGC')
		);
		//开启
		session_start();
	}
	public function userSessionBegin() {	
		//初始化DAO		
		$config = $GLOBALS['config']['db'];
		//配置选择DAO类
		switch ($GLOBALS['config']['app']['dao']){
			case 'mysql':
					$dao_class = 'MySQLDB';
					break;
			case 'pdo':
					$dao_class = 'PDODB';
					break;
			default:
					break;
		}
		$this->_dao = $dao_class::getInstance($config);
		
	}
	public function userSessionEnd() {
		return true;
	}
	/**
	 * 读操作
	 * 执行时机：	session机制开启程中执行
	 * 工作：		从当前session数据区读取内容
	 * @param $sess_id string
	 * @return string
	 */
	public function userSessionRead($sess_id) {
		//查询
		$sql = "SELECT session_content FROM `{$_prefix}session` WHERE session_id='$sess_id'";
		return (string) $this->_dao->getOne($sql);
	}
	/**
	 * 写操作
	 * 执行时机：	脚本周期结束时，PHP在整理收尾时
	 * 工作：		将当前脚本处理好的session数据，持久化存储到数据库中！
	 * @param $sess_id string
	 * @param $sess_content string 序列化好的session内容字符串
	 * @return bool
	 */
	public function userSessionWrite($sess_id, $sess_content) {

		// 完成写
		$sql = "REPLACE INTO `{$_prefix}session` VALUES ('$sess_id', '$sess_content', unix_timestamp())";
		// $sql = "INSERT INTO `session` VALUES ('$sess_id', '$sess_content') ON DUPLICATE KEY UPDATE session_content='$sess_content', last_time=unix_timestamp()"
		
		return $this->_dao->query($sql);
		
	}
	/**
	 * 删除操作
	 * 执行时机：	调用了session_destroy()销毁session过程中被调用
	 * 工作：		删除当前session的数据区（记录）
	 * @param $sess_id string
	 * @return bool
	 */
	public function userSessionDelete($sess_id) {
		//删除
		$sql = "DELETE FROM `{$_prefix}session` WHERE session_id='$sess_id'";
		return $this->_dao->query($sql);
	}
	/**
	 * 垃圾回收操作
	 * 执行时机：	开启session机制时，有概率的执行
	 * 工作：		删除那些过期的session数据区
	 * @param $max_lifetime
	 * @return bool
	 */
	public function userSessionGC($max_lifetime) {
		//删除
		$sql = "DELETE FROM `{$_prefix}session` WHERE last_time<unix_timestamp()-$max_lifetime";
		return $this->_dao->query($sql);
	}

}

// 需要创建的表
/***************************************************
* create table `{$_prefix}session` (               *
* 	session_id varchar(40) not null default '',    *    
* 	session_content text,                          * 
*	last_time int not null default 0,              *    
* 	primary key(session_id)                        * 
* )charset=utf8 engine=myisam;                     *    
****************************************************/