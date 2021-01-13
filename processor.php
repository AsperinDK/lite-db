<?php

// Получаем список таблиц для вывода в меню
$table_list = array();
try {
	include __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'class.LiteDB.php';
	$liteDB = new LiteDB('default');
	$table_list = $liteDB->query("SHOW TABLES");
} catch (Exception $e) {
	$table_list = FALSE;
}