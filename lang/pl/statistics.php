<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Statistics page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Statystyki',

	'preview_text' => 'To jest podgląd strony statystyk dostępnej w Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Pokazane tutaj dane są generowane losowo i nie odzwierciedlają twojego serwera.',
	'no_data' => 'Użytkownik nie posiada danych na serwerze.',
	'collapse' => 'Zwiń rozmiary albumów',

	'total' => [
		'total' => 'Łącznie',
		'albums' => 'Albumy',
		'photos' => 'Zdjęcia',
		'size' => 'Rozmiar',
	],
	'table' => [
		'username' => 'Właściciel',
		'title' => 'Tytuł',
		'photos' => 'Zdjęcia',
		'descendants' => 'Potomne',
		'size' => 'Rozmiar',
	],
	'punch_card' => [
		'title' => 'Aktywność',
		'photo-taken' => '%d zrobionych zdjęć',
		'photo-taken-in' => '%d zdjęć wykonanych w %d',
		'photo-uploaded' => '%d przesłanych zdjęć',
		'photo-uploaded-in' => '%d zdjęć przesłanych w %d',
		'with-exif' => 'z danymi exif',
		'less' => 'Mniej',
		'more' => 'Więcej',
		'tooltip' => '%d zdjęć na %s',
		'created_at' => 'Data przesłania',
		'taken_at' => 'Dane Exif',
		'caption' => 'Każda kolumna reprezentuje tydzień.',
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