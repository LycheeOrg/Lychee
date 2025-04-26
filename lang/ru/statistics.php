<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Statistics page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Статистика',

	'preview_text' => 'Это предварительный просмотр страницы статистики, доступной в Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Приведенные здесь данные генерируются случайным образом и не отражают состояние вашего сервера.',
	'no_data' => 'У пользователя нет данных на сервере.',
	'collapse' => 'Свернуть размеры альбомов',

	'total' => [
		'total' => 'Всего',
		'albums' => 'Альбомы',
		'photos' => 'Фотографии',
		'size' => 'Размер',
	],
	'table' => [
		'username' => 'Владелец',
		'title' => 'Название',
		'photos' => 'Фотографии',
		'descendants' => 'Потомки',
		'size' => 'Размер',
	],
	'punch_card' => [
		'title' => 'Активность',
		'photo-taken' => '%d фотографии сделаны',
		'photo-taken-in' => '%d фотографии сделаны в %d',
		'photo-uploaded' => '%d фотографий загружено',
		'photo-uploaded-in' => '%d фотографий загружено в %d',
		'with-exif' => 'с exif данными',
		'less' => 'Меньше',
		'more' => 'Больше',
		'tooltip' => '%d фотографии на %s',
		'created_at' => 'Дата загрузки',
		'taken_at' => 'Exif дата',
		'caption' => 'Каждый столбец представляет неделю.',
	],
	'metrics' => [
		'header' => 'Live metrics',
		'a_visitor' => 'A visitor',
		'visitors' => '%d visitors',
		'visit_singular' => '%1$s viewed %2$s',
		'favourite_singular' => '%1$s favourited %2$s',
		'download_singular' => '%1$s downloaded %2$s',
		'shared_singular' => '%1$s shared %2$s',
		'visit_plural' => '%1$s viewed %2$s',
		'favourite_plural' => '%1$s favourited %2$s',
		'download_plural' => '%1$s downloaded %2$s',
		'shared_plural' => '%1$s shared %2$s',
		'ago' => [
			'days' => '%d days ago',
			'day' => 'a day ago',
			'hours' => '%d hours ago',
			'hour' => 'an hour ago',
			'minutes' => '%d minutes ago',
			'few_minutes' => 'a few minute ago',
			'seconds' => 'a few seconds ago',
		],
	],
];