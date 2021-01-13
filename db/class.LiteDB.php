<?php

class LiteDB {

	/**
	 * @var string $db - путь к каталогу базы данных
	 */
	public $db_dir;
	
	/**
	 * Конструктор (создает БД, если ее еще нет)
	 *
	 * @param string $database - название БД
	 */
	public function __construct($database) {
		if (empty($database)) {
			throw new Exception('Не указана база данных');
		} elseif (!is_string($database) || !preg_match("~^[a-z0-9\-_]+$~ui", $database)) {
			throw new Exception('Имя базы данных указано не корректно');
		} else {
			$db_dir = __DIR__ . DIRECTORY_SEPARATOR . $database;
			// создадим каталог, если его еще нет
			if (
				!file_exists($db_dir) && !is_dir($db_dir)
				&&
				FALSE === mkdir($db_dir)
			) {
				throw new Exception('Ошибка при создании каталога для базы данных');
			} else {
				$this->db_dir = $db_dir;
			}
		}
	}

	/**
	 * Выполняет запрос к базе данных
	 * 
	 * @param string $query - текст запроса
	 * @return array|boolean
	 */
	public function query($query) {
		if (empty($this->db_dir)) {
			throw new Exception('Не указана база данных');
		} elseif (FALSE === $pr = $this->parseQuery($query)) {
			return FALSE;
		} else {
			return $this->{$pr['method']}($pr['params']);
		}
	}

	/**
	 * Парсинг запроса
	 *
	 * @param string $query - текст запроса
	 * @return array|boolean
	 */
	private function parseQuery($query) {
		if (!is_string($query) || empty(trim($query))) {
			throw new Exception('Запрос указан не верно');
			return FALSE;
		} else {
			// если запросов несколько - выполняем только первый
			$query = explode(';', $query);
			$query = reset($query);
			$query = trim($query);
			if (preg_match('~^show[\s]+tables$~ui', $query)) {
				return array(
					'method' => 'getTables',
					'params' => array(),
				);
			} elseif (preg_match('~^select[\s]+\*[\s]+from[\s]+`([^`]+)`$~ui', $query, $m)) {
				$table = $m[1];
				return array(
					'method' => 'getRecords',
					'params' => array(
						'table' => $table,
					),
				);
			} elseif (preg_match('~^create[\s]+table[\s]+`([^`]+)`[\s]+\(([\s]*`[^`]+`([\s]*,[\s]*`[^`]+`)*)\)$~ui', $query, $m)) {
				$table = $m[1];
				$fields = preg_match_all('~`([^`]+)`~ui', $m[2], $fields_m);
				$fields = $fields_m[1];
				return array(
					'method' => 'addTable',
					'params' => array(
						'table' => $table,
						'fields' => $fields,
					),
				);
			} elseif (preg_match('~^insert[\s]+into[\s]+`([^`]+)`[\s]+values[\s]+(\([\s]*[\'][^\']*[\'][\s]*([\s]*,[\s]*[\'][^\']*[\'][\s]*)*\)([\s]*,[\s]*\([\s]*[\'][^\']*[\'][\s]*([\s]*,[\s]*[\'][^\']*[\'][\s]*)*\))*).*~ui', $query, $m)) {
				$table = $m[1];
				preg_match_all('~\([\s]*[\'][^\']*[\'][\s]*([\s]*,[\s]*[\'][^\']*[\'][\s]*)*\)~ui', $m[2], $records_m);
				$records = array();
				foreach ($records_m[0] as $record) {
					preg_match_all('~\'([^\']*)\'~ui', $record, $fv_m);
					$records[] = $fv_m[1];
				}
				return array(
					'method' => 'addRecords',
					'params' => array(
						'table' => $table,
						'records' => $records,
					),
				);
			} else {
				throw new Exception('Запрос указан не верно');
				return FALSE;
			}
		}
	}

	/**
	 * Получение списка таблиц
	 *
	 * @param array
	 * @return boolean
	 */
	private function getTables($params) {
		if (empty($this->db_dir)) {
			throw new Exception('Не указана база данных');
		} else {
			$table_list = array();
			foreach (glob($this->db_dir . DIRECTORY_SEPARATOR  . '*.csv') as $tfp) {
				$tn = explode(DIRECTORY_SEPARATOR, $tfp);
				$tn = end($tn);
				$tn = explode('.', $tn);
				array_pop($tn);
				$tn = implode('.', $tn);
				$table_list[] = $tn;
			}
			return $table_list;
		}
		return FALSE;
	}

	/**
	 * Создание таблицы
	 *
	 * @param array
	 * @return boolean
	 */
	private function addTable($params) {
		if (empty($this->db_dir)) {
			throw new Exception('Не указана база данных');
		} elseif (empty($params['fields'])) {
			throw new Exception('Не указаны поля таблицы');
		} elseif (
			FALSE !== ($fp = fopen($this->db_dir . DIRECTORY_SEPARATOR . $params['table'] . '.csv', 'w'))
			&&
			FALSE !== (fputcsv($fp, $params['fields'], ';'))
		) {
			fclose($fp);
			return TRUE;
		}
		return FALSE;
	}

	// private function editTable() {}

	// private function deleteTable() {}

	/**
	 * Получение всех записей таблицы
	 *
	 * @param array
	 * @return boolean
	 */
	private function getRecords($params) {
		$tfp = $this->db_dir . DIRECTORY_SEPARATOR . $params['table'] . '.csv';
		if (empty($this->db_dir)) {
			throw new Exception('Не указана база данных');
		} elseif (!file_exists($tfp) || !is_file($tfp)) {
			throw new Exception('Указанной таблицы не существует');
		} else {
			$records = array();
			if (FALSE !== ($fp = fopen($tfp, 'r'))) {
				while (FALSE !== ($record = fgetcsv($fp, 1000, ";"))) {
					$records[] = $record;
				}
				fclose($fp);
			}
			return $records;
		}
		return FALSE;
	}

	/**
	 * Добавление записи в таблицу
	 *
	 * @param array
	 * @return boolean
	 */
	private function addRecords($params) {
		$tfp = $this->db_dir . DIRECTORY_SEPARATOR . $params['table'] . '.csv';
		if (empty($this->db_dir)) {
			throw new Exception('Не указана база данных');
		} elseif (!file_exists($tfp) || !is_file($tfp)) {
			throw new Exception('Указанной таблицы не существует');
		} else {
			// получаем заголовки
			// для сравнения колличества колонок и колличества значений
			if (
				FALSE !== ($fp = fopen($tfp, 'r'))
				&&
				FALSE !== ($fields = fgetcsv($fp, 1000, ";"))
			) {
				fclose($fp);
				$count_error = FALSE;
				foreach ($params['records'] as $record) {
					if (count($record) !== count($fields)) {
						$count_error = TRUE;
					}
				}
				if ($count_error) {
					throw new Exception('Колличество значений не совпадает с колличеством полей в таблице');
				} elseif (FALSE !== $fp = fopen($tfp, 'a')) {
					foreach ($params['records'] as $record) {
						if (FALSE === fputcsv($fp, $record, ';')) {
							fclose($fp);
							throw new Exception('Ошибка вставки значений в таблицу');
							return FALSE;
						}
					}
					fclose($fp);
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// private function editRecords() {}

	// private function deleteRecords() {}

}