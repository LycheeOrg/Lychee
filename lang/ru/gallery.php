<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Jobs page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Галерея',

	'smart_albums' => 'Умные альбомы',
	'albums' => 'Альбомы',
	'root' => 'Альбомы',

	'original' => 'Оригинал',
	'medium' => 'Средний',
	'medium_hidpi' => 'Средний HiDPI',
	'small' => 'Миниатюра',
	'small_hidpi' => 'Миниатюра HiDPI',
	'thumb' => 'Квадратная миниатюра',
	'thumb_hidpi' => 'Квадратная миниатюра HiDPI',
	'placeholder' => 'Заглушка изображения низкого качества',
	'thumbnail' => 'Миниатюра фото',
	'live_video' => 'Часть видео с живого фото',

	'camera_data' => 'Дата камеры',
	'album_reserved' => 'Все права защищены',

	'map' => [
		'error_gpx' => 'Ошибка при загрузке GPX файла',
		'osm_contributors' => 'Контрибьюторы OpenStreetMap',
	],

	'search' => [
		'title' => 'Поиск',
		'no_results' => 'По вашему запросу ничего не найдено.',
		'searchbox' => 'Поиск…',
		'minimum_chars' => 'Минимум %s символов.',
		'photos' => 'Фотографии (%s)',
		'albums' => 'Альбомы (%s)',
	],

	'smart_album' => [
		'unsorted' => 'Неотсортированные',
		'starred' => 'Избранные',
		'recent' => 'Недавние',
		'public' => 'Публичные',
		'on_this_day' => 'В этот день',
	],

	'layout' => [
		'squares' => 'Квадратные миниатюры',
		'justified' => 'С пропорциями, выровненные',
		'masonry' => 'С пропорциями, кладка',
		'grid' => 'С пропорциями, сетка',
	],

	'overlay' => [
		'none' => 'Нет',
		'exif' => 'Данные EXIF',
		'description' => 'Описание',
		'date' => 'Дата съемки',
	],

	'timeline' => [
		'default' => 'по умолчанию',
		'disabled' => 'отключено',
		'year' => 'Год',
		'month' => 'Месяц',
		'day' => 'День',
		'hour' => 'Час',
	],

	'album' => [
		'header_albums' => 'Альбомы',
		'header_photos' => 'Фотографии',
		'no_results' => 'Здесь ничего нет',
		'upload' => 'Загрузить фотографии',

		'tabs' => [
			'about' => 'О альбоме',
			'share' => 'Поделиться альбомом',
			'move' => 'Переместить альбом',
			'danger' => 'ОПАСНОЕ МЕСТО',
		],

		'hero' => [
			'created' => 'Создан',
			'copyright' => 'Авторские права',
			'subalbums' => 'Подальбомы',
			'images' => 'Фотографии',
			'download' => 'Скачать альбом',
			'share' => 'Поделиться альбомом',
			'stats_only_se' => 'Статистика доступна в версии для поддерживающих пользователей',
		],

		'stats' => [
			'number_of_visits' => 'Number of visits',
			'number_of_downloads' => 'Number of downloads',
			'number_of_shares' => 'Number of shares',
			'lens' => 'Объектив',
			'shutter' => 'Выдержка',
			'iso' => 'ISO',
			'model' => 'Модель',
			'aperture' => 'Диафрагма',
			'no_data' => 'Нет данных',
		],

		'properties' => [
			'title' => 'Название',
			'description' => 'Описание',
			'photo_ordering' => 'Сортировка фотографий по',
			'children_ordering' => 'Сортировка альбомов по',
			'asc/desc' => 'по возрастанию/по убыванию',
			'header' => 'Установить заголовок альбома',
			'compact_header' => 'Использовать компактный заголовок',
			'license' => 'Установить лицензию',
			'copyright' => 'Установить авторские права',
			'aspect_ratio' => 'Установить соотношение сторон миниатюр альбома',
			'album_timeline' => 'Установить режим временной шкалы альбома',
			'photo_timeline' => 'Установить режим временной шкалы фотографий',
			'layout' => 'Установить макет фотографий',
			'show_tags' => 'Установить отображаемые теги',
			'tags_required' => 'Теги обязательны.',
		],
	],

	'photo' => [
		'actions' => [
			'star' => 'Добавить в избранное',
			'unstar' => 'Убрать из избранного',
			'set_album_header' => 'Установить как заголовок альбома',
			'move' => 'Переместить',
			'delete' => 'Удалить',
			'header_set' => 'Заголовок установлен',
		],

		'details' => [
			'exif_data' => 'EXIF data',
			'about' => 'О фотографии',
			'basics' => 'Основное',
			'title' => 'Название',
			'uploaded' => 'Загружено',
			'description' => 'Описание',
			'license' => 'Лицензия',
			'reuse' => 'Использование',
			'latitude' => 'Широта',
			'longitude' => 'Долгота',
			'altitude' => 'Высота',
			'location' => 'Местоположение',
			'image' => 'Изображение',
			'video' => 'Видео',
			'size' => 'Размер',
			'format' => 'Формат',
			'resolution' => 'Разрешение',
			'duration' => 'Длительность',
			'fps' => 'Частота кадров',
			'tags' => 'Теги',
			'camera' => 'Камера',
			'captured' => 'Снято',
			'make' => 'Производитель',
			'type' => 'Тип/Модель',
			'lens' => 'Объектив',
			'shutter' => 'Выдержка',
			'aperture' => 'Диафрагма',
			'focal' => 'Фокусное расстояние',
			'iso' => 'ISO %s',
			'stats' => [
				'header' => 'Statistics',
				'number_of_visits' => 'Number of visits',
				'number_of_downloads' => 'Number of downloads',
				'number_of_shares' => 'Number of shares',
				'number_of_favourites' => 'Number of favourites',
			],
		],

		'edit' => [
			'set_title' => 'Установить название',
			'set_description' => 'Установить описание',
			'set_license' => 'Установить лицензию',
			'no_tags' => 'Нет тегов',
			'set_tags' => 'Установить теги',
			'set_created_at' => 'Установить дату загрузки',
			'set_taken_at' => 'Установить дату съемки',
			'set_taken_at_info' => 'При установке будет отображаться звезда %s, чтобы указать, что эта дата отличается от оригинальной EXIF даты.<br>Снимите галочку и сохраните, чтобы сбросить на оригинальную дату.',
		],
	],

	'nsfw' => [
		'header' => 'Чувствительный контент',
		'description' => 'Этот альбом содержит чувствительный контент, который может быть оскорбительным или disturbing для некоторых людей.',
		'consent' => 'Нажмите для согласия.',
	],

	'menus' => [
		'star' => 'Добавить в избранное',
		'unstar' => 'Убрать из избранного',
		'star_all' => 'Добавить все в избранное',
		'unstar_all' => 'Убрать все из избранного',
		'tag' => 'Тег',
		'tag_all' => 'Тегировать все',
		'set_cover' => 'Установить обложку альбома',
		'remove_header' => 'Удалить заголовок альбома',
		'set_header' => 'Установить заголовок альбома',
		'copy_to' => 'Копировать в …',
		'copy_all_to' => 'Копировать выбранные в …',
		'rename' => 'Переименовать',
		'move' => 'Переместить',
		'move_all' => 'Переместить выбранные',
		'delete' => 'Удалить',
		'delete_all' => 'Удалить выбранные',
		'download' => 'Скачать',
		'download_all' => 'Скачать выбранные',
		'merge' => 'Объединить',
		'merge_all' => 'Объединить выбранные',

		'upload_photo' => 'Загрузить фото',
		'import_link' => 'Импортировать по ссылке',
		'import_dropbox' => 'Импортировать из Dropbox',
		'new_album' => 'Новый альбом',
		'new_tag_album' => 'Новый альбом с тегами',
		'upload_track' => 'Загрузить трек',
		'delete_track' => 'Удалить трек',
	],

	'sort' => [
		'photo_select_1' => 'Время загрузки',
		'photo_select_2' => 'Дата съемки',
		'photo_select_3' => 'Название',
		'photo_select_4' => 'Описание',
		'photo_select_6' => 'Избранное',
		'photo_select_7' => 'Формат фото',
		'ascending' => 'По возрастанию',
		'descending' => 'По убыванию',
		'album_select_1' => 'Время создания',
		'album_select_2' => 'Название',
		'album_select_3' => 'Описание',
		'album_select_5' => 'Самая поздняя дата съемки',
		'album_select_6' => 'Самая ранняя дата съемки',
	],

	'albums_protection' => [
		'private' => 'частный',
		'public' => 'публичный',
		'inherit_from_parent' => 'унаследовать от родителя',
	],
];