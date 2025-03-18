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
	'title' => 'Galeria',

	'smart_albums' => 'Inteligentne albumy',
	'albums' => 'Albumy',
	'root' => 'Albumy',

	'original' => 'Oryginał',
	'medium' => 'Średni',
	'medium_hidpi' => 'Średni HiDPI',
	'small' => 'Miniatura',
	'small_hidpi' => 'Miniaturka HiDPI',
	'thumb' => 'Kwadratowa miniaturka',
	'thumb_hidpi' => 'Kwadratowa miniaturka HiDPI',
	'placeholder' => 'Obraz zastępczy o niskiej jakości',
	'thumbnail' => 'Miniatura zdjęcia',
	'live_video' => 'Część wideo zdjęcia na żywo',

	'camera_data' => 'Dane kamery',
	'album_reserved' => 'Wszelkie prawa zastrzeżone',

	'map' => [
		'error_gpx' => 'Błąd ładowania pliku GPX',
		'osm_contributors' => 'Współtwórcy OpenStreetMap',
	],

	'search' => [
		'title' => 'Wyszukiwanie',
		'searching' => 'Wyszukiwanie...',
		'no_results' => 'Nic nie pasuje do wyszukiwanego hasła.',
		'searchbox' => 'Szukaj...',
		'minimum_chars' => 'Wymagane minimum %s znaków.',
		'photos' => 'Zdjęcia (%s)',
		'albums' => 'Albumy (%s)',
	],

	'smart_album' => [
		'unsorted' => 'Nieposortowane',
		'starred' => 'Wyróżniony',
		'recent' => 'Najnowsze',
		'public' => 'Publiczny',
		'on_this_day' => 'W tym dniu',
	],

	'layout' => [
		'squares' => 'Miniatury kwadratowe',
		'justified' => 'Z aspektem, wyjustowane',
		'masonry' => 'Z aspektem, cegiełki',
		'grid' => 'Z aspektem, siatka',
	],

	'overlay' => [
		'none' => 'Brak',
		'exif' => 'Dane EXIF',
		'description' => 'Opis',
		'date' => 'Data wykonania',
	],

	'timeline' => [
		'default' => 'domyślny',
		'disabled' => 'wyłączony',
		'year' => 'Rok',
		'month' => 'Miesiąc',
		'day' => 'Dzień',
		'hour' => 'Godzina',
	],

	'album' => [
		'header_albums' => 'Albumy',
		'header_photos' => 'Zdjęcia',
		'no_results' => 'Nie ma tu nic do oglądania',
		'upload' => 'Przesyłanie zdjęć',

		'tabs' => [
			'about' => 'Informacje o albumie',
			'share' => 'Udostępnij album',
			'move' => 'Przenieś album',
			'danger' => 'STREFA RYZYKOWNA',
		],

		'hero' => [
			'created' => 'Utworzony',
			'copyright' => 'Prawo autorskie',
			'subalbums' => 'Sub-albumy',
			'images' => 'Zdjęcia',
			'download' => 'Pobierz album',
			'share' => 'Udostępnij album',
			'stats_only_se' => 'Statystyki dostępne w Supporter Edition',
		],

		'stats' => [
			'lens' => 'Obiektyw',
			'shutter' => 'Czas otwarcia migawki',
			'iso' => 'ISO',
			'model' => 'Model',
			'aperture' => 'Przysłona',
			'no_data' => 'Brak danych',
		],

		'properties' => [
			'title' => 'Tytuł',
			'description' => 'Opis',
			'photo_ordering' => 'Sortuj zdjęcia według',
			'children_ordering' => 'Sortuj albumy według',
			'asc/desc' => 'asc/desc',
			'header' => 'Ustawianie nagłówka albumu',
			'compact_header' => 'Użyj kompaktowego nagłówka',
			'license' => 'Ustaw licencję',
			'copyright' => 'Ustaw prawo autorskie',
			'aspect_ratio' => 'Ustawianie proporcji miniatury albumu',
			'album_timeline' => 'Ustawianie trybu osi czasu albumu',
			'photo_timeline' => 'Ustawianie trybu osi czasu zdjęć',
			'layout' => 'Ustawianie układu zdjęć',
			'show_tags' => 'Ustawianie wyświetlania tagów',
			'tags_required' => 'Wymagane są znaczniki.',
		],
	],

	'photo' => [
		'actions' => [
			'star' => 'Wyróżnienie',
			'unstar' => 'Cofnij wyróżnienie',
			'set_album_header' => 'Ustaw jako nagłówek albumu',
			'move' => 'Przenieś',
			'delete' => 'Usuń',
			'header_set' => 'Ustaw nagłówki',
		],

		'details' => [
			'about' => 'O',
			'basics' => 'Podstawy',
			'title' => 'Tytuł',
			'uploaded' => 'Przesłano',
			'description' => 'Opis',
			'license' => 'Licencja',
			'reuse' => 'Ponowne użycie',
			'latitude' => 'Szerokość geograficzna',
			'longitude' => 'Długość geograficzna',
			'altitude' => 'Wysokość',
			'location' => 'Lokalizacja',
			'image' => 'Obraz',
			'video' => 'Wideo',
			'size' => 'Rozmiar',
			'format' => 'Format',
			'resolution' => 'Rozdzielczość',
			'duration' => 'Czas trwania',
			'fps' => 'Liczba klatek na sekundę',
			'tags' => 'Tagi',
			'camera' => 'Kamera',
			'captured' => 'Przechwycony',
			'make' => 'Marka',
			'type' => 'Typ/Model',
			'lens' => 'Obiektyw',
			'shutter' => 'Czas otwarcia migawki',
			'aperture' => 'Przysłona',
			'focal' => 'Ogniskowa',
			'iso' => 'ISO %s',
		],

		'edit' => [
			'set_title' => 'Ustaw tytuł',
			'set_description' => 'Ustaw opis',
			'set_license' => 'Ustaw licencję',
			'no_tags' => 'Brak tagów',
			'set_tags' => 'Ustaw tagi',
			'set_created_at' => 'Ustaw datę przesłania',
			'set_taken_at' => 'Ustaw datę wykonania',
			'set_taken_at_info' => 'Po ustawieniu wyświetlona zostanie gwiazdka %s wskazująca, że ta data nie jest oryginalną datą EXIF.<br>Zaznacz pole wyboru i zapisz, aby zresetować do oryginalnej daty.',
		],
	],

	'nsfw' => [
		'header' => 'Wrażliwa zawartość',
		'description' => 'Ten album zawiera wrażliwe treści, które niektórzy mogą uznać za obraźliwe lub niepokojące.',
		'consent' => 'Kliknij, aby wyrazić zgodę.',
	],

	'menus' => [
		'star' => 'Wyróżnienie',
		'unstar' => 'Cofnij wyróżnienie',
		'star_all' => 'Wybrano wyróżnienie',
		'unstar_all' => 'Cofnij wyróżnienie dla zaznaczonych',
		'tag' => 'Tag',
		'tag_all' => 'Otaguj zaznaczone',
		'set_cover' => 'Ustaw okładkę albumu',
		'remove_header' => 'Usuń nagłówek albumu',
		'set_header' => 'Ustawianie nagłówka albumu',
		'copy_to' => 'Kopiuj do ...',
		'copy_all_to' => 'Kopiuj wybrane do ...',
		'rename' => 'Zmiana nazwy',
		'move' => 'Przenieś',
		'move_all' => 'Przenieś wybrane',
		'delete' => 'Usuń',
		'delete_all' => 'Usuń zaznaczone',
		'download' => 'Pobierz',
		'download_all' => 'Pobierz wybrane',
		'merge' => 'Scal',
		'merge_all' => 'Scal wybrane',

		'upload_photo' => 'Prześlij zdjęcie',
		'import_link' => 'Import z łącza',
		'import_dropbox' => 'Import z Dropbox',
		'new_album' => 'Nowy album',
		'new_tag_album' => 'Nowy album z tagami',
		'upload_track' => 'Prześlij ścieżkę',
		'delete_track' => 'Usuń ścieżkę',
	],

	'sort' => [
		'photo_select_1' => 'Czas przesyłania',
		'photo_select_2' => 'Czas wykonania',
		'photo_select_3' => 'Tytuł',
		'photo_select_4' => 'Opis',
		'photo_select_6' => 'Wyróżnienie',
		'photo_select_7' => 'Format zdjęcia',
		'ascending' => 'Rosnąco',
		'descending' => 'Malejąco',
		'album_select_1' => 'Czas utworzenia',
		'album_select_2' => 'Tytuł',
		'album_select_3' => 'Opis',
		'album_select_5' => 'Dane ostatniego użycia',
		'album_select_6' => 'Najstarsze',
	],

	'albums_protection' => [
		'private' => 'prywatny',
		'public' => 'publiczny',
		'inherit_from_parent' => 'dziedziczą po rodzicu',
	],
];