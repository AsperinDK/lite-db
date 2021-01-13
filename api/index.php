<?php
/**
 * API для работы с БД
 * сюда обращаются js скрипты с ajax запросами
 *
 * @param string $_REQUEST['query'] - запрос к БД
 * @return json
 */

// Структура ответа API
$result = array(
	'result' => FALSE,
	'data' => NULL,
	'message' => array(),
);

// Совершение запроса
try {
	include __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'class.LiteDB.php';
	$liteDB = new LiteDB('default');
	if (FALSE !== ($result['data'] =  $liteDB->query(isset($_REQUEST['query']) ? $_REQUEST['query'] : NULL))) {
		$result['result'] = TRUE;
	}
} catch (Exception $e) {
	$result['message'][] = $e->getMessage();
}

// Возвращаем ответ в формате json
header('Content-Type: application/json');
print json_encode($result);