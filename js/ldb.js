$(function() {
	ldb.init();
});


var ldb = ldb || {};

// Инициализирует UI на странице
ldb.init = function() {
	ldb.table_list = $('#ldb-list');
	ldb.editor = $('#ldb-editor');
	ldb.create_table = $('#ldb-createTable');

	// создание таблицы
	ldb.create_table.on('click', function(e) {
		e.preventDefault();
		ldb.showCreateTableForm();
	});

	// выбор таблицы из списка
	ldb.table_list.on('click', 'a', function(e) {
		e.preventDefault();
		ldb.showTable($(this).text());
	});
}

// Выводит в редактор форму создания таблицы и инициализирует ее
ldb.showCreateTableForm = function() {
	var form = $(
		'<form name="create_table" class="w-100">' +
			'<h4 class="mb-4">Создание новой таблицы</h4>' +
			'<div class="mb-4">' +
				'<div class="form-label">Введите название таблицы</div>' +
				'<input type="text" class="form-control" name="table_name">' +
				'<div class="form-text">Разрешено использовать буквы, цифры, пробелы, скобки</div>' +
			'</div>' +
			'<div class="mb-3">' +
				'<div class="form-label">Укажите названия полей</div>' +
				'<div class="row">' +
					'<div class="col-6">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №1" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №2" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №3" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №4" name="field">' +
					'</div>' +
					'<div class="col-6">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №5" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №6" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №7" name="field">' +
						'<input type="text" class="form-control mb-2" placeholder="Поле №8" name="field">' +
					'</div>' +
				'</div>' +
			'</div>' +
			'<button type="submit" class="btn btn-warning">Создать таблицу</button>' +
		'</form>'
	);
	ldb.editor.html('').append(form);
	// создание таблицы
	form.on('submit', function(e) {
		e.preventDefault();
		// собираем значения полей
		var tn = $.trim($('[name=table_name]').val());
		var fl = [];
		$('[name=field]').each(function() {
			if ($.trim(this.value)) {
				fl.push(this.value);
			}
		});
		// собираем ошибки формы, чтобы вывести разом
		var err = [];
		if (!tn) {
			err.push('Забыли указать название таблицы');
		}
		if (!fl.length) {
			err.push('Забыли указать названия полей');
		}
		if (err.length) {
			console.error(err.join("\n"));
			alert(err.join("\n"));
		} else {
			// формируем запрос
			var query = 'CREATE TABLE `' + tn + '` (`' + fl.join('`, `') + '`)';
			// отправка данных на сервер
			$.ajax({
				url: 'api/',
				data: {query: query},
				dataType: 'json',
				type: 'POST',
				complete: function(jqXHR, status) {
					if ('success' !== status) {
						console.error('Ajax error. Перезагрузите страницу');
						alert('Ajax error. Перезагрузите страницу');
					}
				},
				success: function(data){
					if (!data.result) {
						console.error(data.message.join("\n"));
						alert(data.message.join("\n"));
					} else {
						// обновить список таблиц
						ldb.table_list.append('<li class="list-group-item"><a href="#">' + tn + '</a></li>')
						// вывод на экран данных таблицы
						ldb.showTable(tn);
					}
				}
			});
		}
	});
}

// Выводит в редактор данные таблицы с формой добавления значений и инициализирует ее
ldb.showTable = function(table_name) {
	// вешаем лоадер на редактор
	ldb.editor.html('Загрузка...');
	// получаем данные таблицы
	$.ajax({
		url: 'api/',
		data: {query: 'SELECT * FROM `' + table_name + '`'},
		dataType: 'json',
		type: 'POST',
		complete: function(jqXHR, status) {
			if ('success' !== status) {
				console.error('Ajax error. Перезагрузите страницу');
				alert('Ajax error. Перезагрузите страницу');
			}
		},
		success: function(data){
			if (!data.result) {
				console.error(data.message.join("\n"));
				alert(data.message.join("\n"));
			} else {
				// выводим название таблицы и ее структуру
				var form = $(
					'<form class="w-100 h-100">' +
						'<h4 class="table-name"><span class="fw-normal text-muted">Таблица</span> &laquo;' + table_name + '&raquo;</h4>' +
						'<table class="table mb-0">' +
							'<thead>' +
								'<tr>' +
									'<th scope="col">&nbsp;</th>' +
									'<th scope="col">' + data.data[0].join('</th><th scope="col">') + '</th>' +
								'</tr>' +
							'</thead>' +
							'<tbody></tbody>' +
						'</table>' +
					'</form>'
				);
				var tbody = $('tbody', form);
				// выводим данные таблицы
				$.each(data.data, function(k, v) {
					if (0 !== k) {
						tbody.append(
							'<tr>' +
								'<td class="text-muted">' + (k + 1) + '</td>' +
								'<td>' + v.join('</td><td>') + '</td>' +
							'</tr>'
						);
					}
				});
				// выволдим редактор
				tbody.append(
					'<tr class="actions">' +
						'<td><button class="btn btn-warning btn-sm fw-bold">+</button></td>' +
						new Array(data.data[0].length+1).join('<td><input type="text" class="form-control form-control-sm" name="field"></td>') +
					'</tr>'
				);
				ldb.editor.html('').append(form);
				// инициализируем редактор
				form.on('submit', function(e) {
					e.preventDefault();
					// собираем значения полей
					var fl = [];
					var empty = true;
					$('[name=field]').each(function() {
						fl.push(this.value);
						if ($.trim(this.value)) {
							empty = false;
						}
					});
					if (empty) {
						console.error('Забыли указать названия полей');
						alert('Забыли указать названия полей');
					} else {
						// формируем запрос
						var query = 'INSERT INTO `' + table_name + '` VALUES (\'' + fl.join('\',\'') + '\')';
						// отправка данных на сервер
						$.ajax({
							url: 'api/',
							data: {query: query},
							dataType: 'json',
							type: 'POST',
							complete: function(jqXHR, status) {
								if ('success' !== status) {
									console.error('Ajax error. Перезагрузите страницу');
									alert('Ajax error. Перезагрузите страницу');
								}
							},
							success: function(data){
								if (!data.result) {
									console.error(data.message.join("\n"));
									alert(data.message.join("\n"));
								} else {
									// обновление данных таблицы
									ldb.showTable(table_name);
								}
							}
						});
					}
				});
			}
		}
	});
}