<?php

/**
 *=================================================================== 
 * Factory.class.php 工厂类
 * @author 秦佳奇
 * @version 1.0
 * 2016年5月20日22:10:38 
 *===================================================================
 */

class Factory {

	public static function makeModel($modelName){
		//存储模型对象列表
		static $modelList = array();
		if(!isset($modelList[$modelName])){
			//未实例化则实例化
			$modelList[$modelName] = new $modelName;
		}
		return $modelList[$modelName];
	}
}




