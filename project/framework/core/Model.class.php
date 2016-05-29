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
	 * function : 转义数组中所有的数据
	 * @param : $data array 要转义的数据
	 * @return : $data array 转义后的数据
	 */
	protected function _escapeStringAll($data){
		foreach ($data as $key => $value) {
			$data[$key] = $this->_dao->escapeString($value) ;
		}
		return $data;
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
	protected function _insertRecord($list){
		//集中转义数据
		$list = $this->_escapeStringAll($list);
		//字段列表字符串
		$fieldList = '';
		//值列表字符串
		$valueList = '';
		//构造插入列表
		foreach ($list as $key => $value) {
			if(in_array($key, $this->_fields)){
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
	 * function : 更新记录
	 * @param : $list array 要更新的字段名和值的关联数组
	 * @return : mixed 成功返回受影响行数， 失败返回false
	 */
	protected function _updateRecord($list){
		//集中转义数据
		$list = $this->_escapeStringAll($list);
		//更新字段列表
		$upList = '';
		//更新条件
		$condition = 0;

		//构建更新字符串
		foreach ($list as $key => $value) {
			if(in_array($key, $this->_fields)){
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
	 * function : 删除记录，可删除一个或多个条目
	 * @param : $id mixed 传入要删除条目的主键ID，可以是一个数组
	 * @return : mixed 成功：返回成功删除的记录数 失败： false
	 */
	protected function _deleteRecord($id){
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
	protected function _getRecord($id){
	
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
	protected function _getTotalNum($condition = false){
		if($condition === false){
			$sql = "SELECT count(*) FROM $this->_table ;";
		} else {
			$sql = "SELECT count(*) FROM $this->_table WHERE $condition;";
		}
		return $this->_dao->getOne($sql);
	}


	

	

	

	
}

