<?php

/**
 *=================================================================== 
 * MySQLDB.class.php MySQL数据库处理类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class MySQLDB implements I_DAO{

	//mysql参数
	private $_host;
	private $_port;
	private $_username;
	private $_password;
	private $_charset;
	private $_dbname;

	//单例对象
	private static $_instance;

	//mysql资源
	private $_resource;

	/*
	 * function: 获取单例对象
	 * @param :  array $config 参数初始化数组
	 * @return : object self::$_instance 单例对象
	*/
	public static function getInstance($config){
		if(!isset(self::$_instance)){
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	/*
	 * function: 构造方法
	 * @param :  array $config 参数初始化数组
	 * @return : void
	*/
	private function __construct($config){
		$this->_host = isset($config['host']) ? $config['host'] : 'localhost';
		$this->_port = isset($config['port']) ? $config['port'] : '3306';
		$this->_username = isset($config['username']) ? $config['username'] : 'username';
		$this->_password = isset($config['password']) ? $config['password'] : 'password';
		$this->_charset = isset($config['charset']) ? $config['charset'] : 'utf8';
		$this->_dbname = isset($config['dbname']) ? $config['dbname'] : '';

		//连接数据库
		$this->_connect();
		//设置编码
		$this->setCharset($this->_charset);
		//选定数据库
		$this->selectDB($this->_dbname);

	}

	/*
	 * function: 克隆
	*/
	private function __clone(){}

	/*
	 * function: 序列化
	*/
	public function __sleep(){
		echo "serializing...";
		mysql_close($this->_resource);

		return array('host','port','username','password','charset','dbname');
	}
	/*
	 * function: 反序列化
	*/
	public function __wakeup(){
		$this->connect();
		$this->setCharset($this->_charset);
		$this->selectDB($this->_dbname);
	}

	/*
	 * function: 连接数据库
	*/
	private function _connect(){
		$this->_resource = mysql_connect("$this->_host:$this->_port", "$this->_username", "$this->_password") or die("Database connect failed.");


	}

	/*
	 * function: 设定数据库编码
	 * @param :  string $charset 编码类型
	 * @return : void
	*/
	private function setCharset($charset){
		$this->query("set names $charset", $this->_resource);
	}
	/*
	 * function: 选择数据库
	 * @param :  array $config 参数初始化数组
	 * @return : void
	*/
	private function selectDB($dbname){
		$this->query("use $dbname", $this->_resource);
	}


	/*
	 * function: 发送SQL请求
	 * @param :  string $sql SQL语句
	 * @return : success: $result 结果集 failed: false
	*/
	public function query($sql){
		if(!$result = mysql_query($sql, $this->_resource)){
			echo "<br />sql execute failed";
			echo "<br />failed SQL:" . $sql;
			echo "<br />failed infomation:" . mysql_error();
			echo "<br />failed number:" . mysql_errno();
			die;
		}
		return $result;
	}

	/*
	 * function: 执行查询，返回二维数组形式的所有数据
	 * @param :  string $sql SQL语句
	 * @return : success: $result 结果集 failed: false
	*/
	public function getAll($sql){

		$result = $this->query($sql);

		$result_arr = array();
		while($record = mysql_fetch_assoc($result)){
			$result_arr[] = $record;
		}

		return $result_arr;
	}

	/*
	 * function: 执行查询，返回一行数据
	 * @param :  string $sql SQL语句
	 * @return : success: $result 结果集 failed: false
	*/
	public function getRow($sql){
		$result = $this->query($sql);

		if($record = mysql_fetch_assoc($result)){
			return $record;
		} 
		return false;
	}

	/*
	 * function: 执行查询，返回单个数据
	 * @param :  string $sql SQL语句
	 * @return : success: $result 结果集 failed: false
	*/
	public function getOne($sql){
		$result = $this->query($sql);

		if(false !== $record = mysql_fetch_assoc($result)){
			return $record[0];
		} 
		return false;
	}

	/*
	 * function: 转义用户数据
	 * @param :  string $data 待转义的数据
	 * @return : string $data 转义后的数据
	*/
	public function escapeString($data){
		return "'" . mysql_real_escape_string($data, $this->_resource) . "'";
	}



}

