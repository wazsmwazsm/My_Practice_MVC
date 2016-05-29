<?php

/**
 *=================================================================== 
 * PDODB.class.php PDO数据库处理类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class PDODB implements I_DAO {

	//数据库参数
	private $_host;
	private $_port;
	private $_username;
	private $_password;
	private $_charset;
	private $_dbname;

	//pdo参数
	private $_dsn;
	private $_driverOptions;
	private $_pdo;


	//单例对象
	private static $_instance;

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
		
		//初始化数据库参数
		$this->_initParams($config);
		//初始化DSN
		$this->_initDSN();
		//初始化驱动选项
		$this->_initDriverOptions();
		//初始化驱动选项PDO
		$this->_initPDO();

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
		mysql_close($this->_pdo);

		return array('host','port','username','password','charset','dbname');
	}
	/*
	 * function: 反序列化
	*/
	public function __wakeup(){
		$this->_initDSN();
		$this->_initDriverOptions();
		$this->_initPDO();
	}


	/*
	 * function: 初始化数据库参数
	 * @param :  array $config 参数初始化数组
	 * @return : void
	*/
	private function _initParams($config){
		$this->_host = isset($config['host']) ? $config['host'] : 'localhost';
		$this->_port = isset($config['port']) ? $config['port'] : '3306';
		$this->_username = isset($config['username']) ? $config['username'] : 'username';
		$this->_password = isset($config['password']) ? $config['password'] : 'password';
		$this->_charset = isset($config['charset']) ? $config['charset'] : 'utf8';
		$this->_dbname = isset($config['dbname']) ? $config['dbname'] : '';
	}

	/*
	 * function: 初始化DSN
	*/
	private function _initDSN(){
		$this->_dsn = "mysql:host=$this->_host;port=$this->_port;dbname=$this->_dbname";
	}

	/*
	 * function: 初始化设备驱动选项
	*/
	private function _initDriverOptions(){
		$this->_driverOptions = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $this->_charset",
			);
	}

	/*
	 * function: 初始化PDO
	*/
	private function _initPDO(){
		$this->_pdo = new PDO($this->_dsn, $this->_username, $this->_password, $this->_driverOptions);
	}


	/*
	 * function: 发送SQL请求
	 * @param :  string $sql SQL语句
	 * @return : success: $result 结果集 failed: false
	*/
	public function query($sql){
		if(!$result = $this->_pdo->query($sql)){
			$errorInfo = $this->_pdo->errorInfo();
			echo "<br />sql execute failed";
			echo "<br />failed SQL:" . $sql;
			echo "<br />failed infomation:" . $errorInfo[2];
			echo "<br />failed number:" . $errorInfo[1];
			die;
		}
		return $result;
	}

	/*
	 * function: 执行查询，返回二维数组形式的所有数据
	 * @param :  string $sql SQL语句
	 * @return : success: $list 结果集 failed: false
	*/
	public function getAll($sql){

		$result = $this->query($sql);

		$list = $result->fetchAll(PDO::FETCH_ASSOC);

		$result->closeCursor();

		return $list;
	}

	/*
	 * function: 执行查询，返回一行数据
	 * @param :  string $sql SQL语句
	 * @return : success: $row 结果集 failed: false
	*/
	public function getRow($sql){
		$result = $this->query($sql);

		$row = $result->fetch(PDO::FETCH_ASSOC);

		$result->closeCursor();

		return $row;
	}

	/*
	 * function: 执行查询，返回单个数据
	 * @param :  string $sql SQL语句
	 * @return : success: $string 结果集 failed: false
	*/
	public function getOne($sql){
		$result = $this->query($sql);

		$string = $result->fetchColumn();

		$result->closeCursor();

		return $string;
	}

	/*
	 * function: 获取上一部insert操作产生的ID
	 * @return : string ID
	*/
	public function getInsertId(){
		return $this->_pdo->lastInsertId();
	}

	/*
	 * function: 执行SQL语句,返回执行SQL语句后受影响的行数
	 * @param $sql string SQL语句
	 * @return : string row
	*/
	public function getEffCount($sql){
		return $this->_pdo->exec($sql);
	}

	/*
	 * function: 转义用户数据
	 * @param :  string $data 待转义的数据
	 * @return : string  转义后的数据
	*/
	public function escapeString($data){
		return $this->_pdo->quote($data);
	}





}

