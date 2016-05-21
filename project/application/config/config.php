<?php

return array(
	'db' => array(
		'host' => 'localhost',
		'port' => '3306',
		'username' => 'shop34',
		'password' => 'wzdanzsm',
		'charset' => 'utf8',	
		'dbname' => 'shop34',
		),
	'app' => array(
		'default_platform' => 'admin',
		'dao' => 'pdo',
		'table_prefix' => 'pre_',
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