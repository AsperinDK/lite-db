<?php include __DIR__ . DIRECTORY_SEPARATOR . 'processor.php'; ?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lite Data Base</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
	<link rel="stylesheet" href="css/main.css">
</head>
<body class="bg-light">
	<div class="container">
		<main>
			<div class="py-5 text-center">
				<h1 class="mb-3">Lite Data Base</h1>
				<p class="lead">
					Самописная SQL СУБД с хранением данных в CSV файлах.<br>
					Реализованная в качестве тестового задания.<br>
					<a target="_blank" href="doc/tz.docx"><small>Скачать ТЗ</small></a> |
					<a target="_blank" href="https://github.com/AsperinDK/lite-db"><small>Репозиторий на github</small></a>
				</p>
			</div>
			<div class="row">
				<div class="col-3">
					<h4 class="mt-4">Список таблиц</h4>
					<ul id="ldb-list" class="list-group list-group-flush my-3">
						<? if (FALSE === $table_list) { ?>
							<li class="list-group-item text-danger">Ошибка при получении списка таблиц</li>
						<? } else { ?>
							<? foreach ($table_list as $table) { ?>
								<li class="list-group-item"><a href="#"><?= htmlspecialchars($table) ?></a></li>
							<? } ?>
						<? } ?>
					</ul>
					<div id="ldb-createTable" class="btn d-block btn-outline-secondary mb-4">Создать новую таблицу</div>
				</div>
				<div class="col-9 bg-light">
					<div id="ldb-editor" class="bg-white h-100 p-4 d-flex justify-content-center align-items-center">
						<p class="m-0 text-muted">Выберите таблицу или создайте новую</p>
					</div>
				</div>
			</div>
		</main>
	</div>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script type="text/javascript" src="js/ldb.js"></script>
	<script type="text/javascript">
		$(function() {
			ldb.init();
		});
	</script>
</body>
</html>