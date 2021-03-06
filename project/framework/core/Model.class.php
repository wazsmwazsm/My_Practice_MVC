<?php

/**
 *=================================================================== 
 * Model.class.php 基础模型类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class Model {

	//dao对象
	protected $_dao;
	//表名
	protected $_table;
	//表中字段名
	protected $_fields;

	/*
	 * function : 构造方法
	 */
	public function __construct(){
		$this->_initDAO();
		$this->_initTable();
		$this->_getFields();
	}

	/*
	 * function : 属性重载，外部设置一些访问受限属性
	 * 这里暂时用来修改表名，同一类模型进行多表操作时切换表的作用
	 * 谨慎使用
	 */
	public function __set($property, $value){
		$allow_set_list = array('_table');
		//没有加_自动添加
		if(substr($property, 0, 1) !== '_'){
			$property = '_' . $property;
		}

		if(!in_array($property, $allow_set_list)){
			//访问不允许的属性
			return false;
		}

		$this->$property = $value;
	}

	/*
	 * function : 初始化DAO
	 */
	protected function _initDAO(){
		$config = $GLOBALS['config']['db'];

		//选择数据库驱动
		switch ($GLOBALS['config']['app']['dao']) {
			case 'mysql':
				$dao_class = 'MySQLDB';
				break;
			case 'pdo':
				$dao_class = 'PDODB';
				break;
			default:
				break;
		}

		//初始化DAO对象
		$this->_dao = $dao_class::getInstance($config);

	}


	/*
	 * function : 转义参数中所有的数据
	 * @param : $data mixed 要转义的数据
	 * @return : $data mixed 转义后的数据
	 */
	public function escapeStringAll($data){
		//输入为空？
		if(empty($data)){
			return $data;
		}
		//处理数组(单维和多维)和单个数据
		return is_array($data) ?
			   array_map(array($this,"escapeStringAll"), $data) : 
			   $this->_dao->escapeString($data);		
	}

	/*
	 * function : 转义所有的html字符为html实体
	 * @param : $data mixed 要转义的数据
	 * @return : $data mixed 转义后的数据
	 */
	public function escapeHtmlAll($data){
		//输入为空？
		if(empty($data)){
			return $data;
		}
		//处理数组(单维和多维)和单个数据
		return is_array($data) ?
			   array_map(array($this,"escapeHtmlAll"), $data) : 
			   htmlspecialchars($data);		
	}

/********************************************************************
 *                                                                  *
 *     下面是关于表操作的一些基础功能，有待完善                     *    
 *     将常用的查询和表操作进行封装                                 *
 *                                                                  *
 *                                                                  *
 *                                                                  *
 ********************************************************************/

	/*
	 * function : 拼凑表名，加前缀
	 */
	protected function _initTable(){
		$this->_table = '`' . $GLOBALS['config']['app']['table_prefix'] . $this->_logicTable . '`';

	}


	/*
	 * function : 获取表的字段名
	 */
	protected function _getFields(){
		$this->_fields = array();
		$sql = "DESC " . $this->_table;
		$result = $this->_dao->getAll($sql);

		//获取字段列表
		foreach ($result as $value) {
			$this->_fields[] = $value['Field'];
			//判断主键
			if($value['Key'] == 'PRI'){
				$this->_fields['pk'] = $value['Field'];
			}
		}
	}


	/*
	 * function : 插入记录
	 * @param : $list array 要插入的字段名和值的关联数组
	 * @return : mixed 成功返回插入ID， 失败返回false
	 */
	public function insertRecord($list){
		//输入为空？
		if(empty($list)){
			return false;
		}
		//集中转义数据
		$list = $this->escapeStringAll($list);
		$list = $this->escapeHtmlAll($list);
		//字段列表字符串
		$fieldList = '';
		//值列表字符串
		$valueList = '';
		
		//构造插入列表
		foreach ($list as $key => $value) {
			if(in_array($key, $this->_fields)){
				//防止有键值而无值的情况下插入出错
				if($value == ''){
					$value = "' '";
				}
				//字段名称需要'`'防止关键字重名
				$fieldList .= "`" . $key . "`" . ",";
				$valueList .=  $value . ",";
			}
		}

		//去除最右的逗号
		$fieldList = rtrim($fieldList, ',');
		$valueList = rtrim($valueList, ',');

		$sql = "INSERT INTO $this->_table ({$fieldList}) VALUES ({$valueList}) ";

		if($this->_dao->query($sql)) {
			// 插入成功
			return $this->_dao->getInsertId();
		} else {
			// 插入失败
			return false;
		}
	}

	/*
	 * function : 插入多行记录
	 * @param : $list array 二维数组
	 */
	public function insertRecordAll($data){
		foreach ($data as $value) {
			if(!$this->insertRecord($value)){
				//有一条记录插入失败,则全部停止
				return false;
			}
		}
		return true;
	}


	/*
	 * function : 更新记录
	 * @param : $list array 要更新的字段名和值的关联数组
	 * @return : mixed 成功返回受影响行数， 失败返回false
	 */
	public function updateRecord($list){
		//输入为空？
		if(empty($list)){
			return false;
		}
		//集中转义数据
		$list = $this->escapeStringAll($list);
		$list = $this->escapeHtmlAll($list);
		//更新字段列表
		$upList = '';
		//更新条件
		$condition = 0;

		//构建更新字符串
		foreach ($list as $key => $value) {
			if(in_array($key, $this->_fields)){
				//防止有键值而无值的情况下插入出错
				if($value == ''){
					$value = "' '";
				}
				if($key == $this->_fields['pk']){
					//是主键， 不手动更新， 同时构造条件
					//取得当前主键的值
					$condition = "`$key` = $value";
				} else {
					//需要更新的数据
					$upList .= "`$key` = $value" . ","; 
				}

			}
		}

		//去除右边逗号
		$upList = rtrim($upList, ',');
		//构建更新语句
		$sql = "UPDATE $this->_table SET {$upList} WHERE {$condition}";
		
		//执行更新并判断受影响行数
		if($row = $this->_dao->getEffCount($sql)){
			//有数据更新
			return $row;
		} else {
			return false;
		}
	}

	/*
	 * function : 更新多行记录
	 * @param : $list array 二维数组
	 */
	public function updateRecordAll($data){
		//记录受影响行数的数组
		$effectCount = 0;
		foreach ($data as $value) {
			if($countList[] = $this->updateRecord($value)){
				$effectCount++;
			}			
		}
		
		//返回受影响的行数
		return $effectCount;
	}


	/*
	 * function : 删除记录，可删除一个或多个条目
	 * @param : $id mixed 传入要删除条目的主键ID，可以是一个数组
	 * @return : mixed 成功：返回成功删除的记录数 失败： false
	 */
	public function deleteRecord($id){
		//删除的条件
		$condition = 0; 
		if(is_array($id)){
			//是一组ID
			$condition = "`{$this->_fields['pk']}` IN (". implode(',', $id) .")";
		} else {
			//是一个ID
			$condition = "`{$this->_fields['pk']}`={$id}";
		}

		//构建删除语句
		$sql = "DELETE FROM $this->_table WHERE $condition";

		//执行删除并判断受影响行数
		if($row = $this->_dao->getEffCount($sql)){
			return $row;
		} else {
			return false;
		}
	}

	/*
	 * function : 取得一条或多条记录信息
	 * @param : $id mixed 需要查询条目的主键ID或需要查询条目的主键ID列表
	 * @return : array 返回记录
	 */
	public function getRecord($id){
	
		if(is_array($id)){
			//是一组ID		
			$sql = "SELECT * FROM $this->_table WHERE `{$this->_fields['pk']}` IN (". implode(',', $id) .")";
			return $this->_dao->getAll($sql);

		} else {
			//是一个ID
			$sql = "SELECT * FROM $this->_table WHERE `{$this->_fields['pk']}`=$id";
			return $this->_dao->getRow($sql);
		}

	}


	/*
	 * function : 获取满足条件的总记录数
	 * @param : string $condition 查询条件 exp:'id=1'
	 * @return : number 返回查询的记录数
	 */
	public function getTotalNum($condition = false){
		if($condition === false){
			$sql = "SELECT count(*) FROM $this->_table ;";
		} else {
			$sql = "SELECT count(*) FROM $this->_table WHERE $condition;";
		}
		return $this->_dao->getOne($sql);
	}

	/*
	 * function : 获取满足条件的一条记录
	 * @param : string $condition 查询条件 exp:'id=1'
	 * @return : array 返回查询的记录
	 */
	public function getCRecord($condition = false){
		//条件为空
		if($condition === false){
			return false;
		} 

		$sql = "SELECT * FROM $this->_table WHERE $condition;";
		
		return $this->_dao->getRow($sql);
	}

	/*
	 * function : 获取满足条件的所有记录
	 * @param : string $condition 查询条件 exp:'id=1'
	 * @return : array 返回查询的记录 二维数组
	 */
	public function getAllCRecord($condition = false){
		//条件为空
		if($condition === false){
			return false;
		} 

		$sql = "SELECT * FROM $this->_table WHERE $condition;";
		
		return $this->_dao->getAll($sql);
	}


	/*
	 * function : 获取表中所有记录
	 * @return : array 所有的条目
	 */
	public function getAllRecord(){		
		$sql = "SELECT * FROM $this->_table";		
		return $this->_dao->getAll($sql);
		
	}


	/*
	 * function : 获取LIMIT后的条目信息
	 * @param : $offset int 偏移量
	 * @param : $limit int 每次取记录条数
	 * @return : $condition string where查询条件
	 */
	public function getLimit($offset, $limit, $condition=false){
		if($condition === false){
			$sql = "SELECT * FROM {$this->_table} LIMIT $offset, $limit";
		} else {
			$sql = "SELECT * FROM {$this->_table} where $condition LIMIT $offset, $limit";
		}
		return $this->_dao->getAll($sql);
	}

	

	

	
}

