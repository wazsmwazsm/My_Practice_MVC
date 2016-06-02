<?php

return array(
	'db' => array(
		'host' => 'localhost',
		'port' => '3306',
		'username' => 'shop',
		'password' => 'wzdanzsm',
		'charset' => 'utf8',	
		'dbname' => 'shop',
		'sql_log' => false,
		),
	'app' => array(
		'default_platform' => 'admin',
		'dao' => 'pdo',
		'table_prefix' => 'cz_',
		),
	'admin'=>array(
		'default_controller' => 'Index',
		'default_action' => 'index',
		),
	'home' => array(
		'default_controller' => 'Index',
		'default_action' => 'index',
		),

	);