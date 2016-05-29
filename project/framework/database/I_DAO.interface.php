<?php

/**
 *=================================================================== 
 * I_DAO.interface.php DAO(Database Access Object)接口
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */
interface I_DAO{
	
	//对DAO类的规定
	public static function getInstance($config);
	public function query($sql);
	public function getAll($sql);
	public function getRow($sql);
	public function getOne($sql);
	public function getInsertId();
	public function getEffCount($sql);
	public function escapeString($sql);
}





