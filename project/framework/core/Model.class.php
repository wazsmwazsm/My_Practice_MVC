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


	/*
	 * function : 构造方法
	 */
	public function __construct(){
		$this->_initDAO();
		$this->_initTable();
	}

	/*
	 * function : 初始化DAO
	 */
	protected function _initDAO(){
		$config = $GLOBALS['config']['db'];

		//选择数据库驱动
		switch ($GLOBALS['config']['app']['dao']) {
			case  'mysql':
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

/********************************************************************
 *                                                                  *
 *     下面是关于表操作的一些基础功能，有待完善                     *    
 *                                                                  *
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
	 * function : 获取总记录数
	 * @param : string $where 查询条件 exp:'id=1'
	 * @return : number 返回查询的记录数
	 */
	public function total($where){
		if(empty($where)){
			$sql = "select count(*) from $this->_table ;";
		} else {
			$sql = "select count(*) from $this->_table where $where;";
		}
		return $this->_dao->getOne($sql);
	}


	/*
	 * function : 更新记录
	 * @param : 
	 * @return : 
	 */

	/*
	 * function : 插入记录
	 * @param : 
	 * @return : 
	 */

	/*
	 * function : 删除记录
	 * @param : 
	 * @return : 
	 */

	
}

