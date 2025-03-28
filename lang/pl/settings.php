<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Ustawienia',
	'small_screen' => 'Aby uzyskać lepsze wrażenia na stronie ustawień,<br />zalecamy korzystanie z większego ekranu.',
	'tabs' => [
		'basic' => 'Podstawowe',
		'all_settings' => 'Wszystkie ustawienia',
	],
	'toasts' => [
		'change_saved' => 'Zmiana zapisana!',
		'details' => 'Ustawienia zostały zmodyfikowane zgodnie z prośbą',
		'error' => 'Błąd!',
		'error_load_css' => 'Nie można załadować dist/user.css',
		'error_load_js' => 'Nie można załadować dist/custom.js',
		'error_save_css' => 'Nie można zapisać CSS',
		'error_save_js' => 'Nie można zapisać JS',
		'thank_you' => 'Dziękujemy za wsparcie.',
		'reload' => 'Odśwież stronę, aby uzyskać pełną funkcjonalność.',
	],
	'system' => [
		'header' => 'System',
		'use_dark_mode' => 'Użyj trybu ciemnego dla Lychee',
		'language' => 'Język używany przez Lychee',
		'nsfw_album_visibility' => 'Wrażliwe albumy domyślnie widoczne.',
		'nsfw_album_explanation' => 'Jeśli album jest publiczny, nadal jest dostępny, tylko ukryty przed widokiem i <b>można go ujawnić, naciskając <kbd>H</kbd></b>.',
		'cache_enabled' => 'Włącz buforowanie odpowiedzi.',
		'cache_enabled_details' => 'Znacznie przyspieszy to czas reakcji Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Jeśli używasz albumów chronionych hasłem, nie powinieneś włączać tej opcji.',
	],
	'lychee_se' => [
		'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
		'call4action' => 'Uzyskaj ekskluzywne funkcje i wspieraj rozwój Lychee. Odblokuj <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">EdycjęSE</a>.',
		'preview' => 'Włącz podgląd funkcji Lychee SE',
		'hide_call4action' => 'Ukryj ten formularz rejestracyjny Lychee SE. Jestem zadowolony z Lychee w obecnej formie. :)',
		'hide_warning' => 'Jeśli ta opcja jest włączona, jedynym sposobem zarejestrowania klucza licencyjnego będzie skorzystanie z powyższej karty Więcej. Zmiany zostaną zastosowane po przeładowaniu strony.',
	],
	'dropbox' => [
		'header' => 'Dropbox',
		'instruction' => 'Aby zaimportować zdjęcia z Dropbox, potrzebujesz ważnego klucza aplikacji drop-ins z ich strony internetowej.',
		'api_key' => 'Klucz API Dropbox',
		'set_key' => 'Ustawianie klucza Dropbox',
	],
	'gallery' => [
		'header' => 'Galeria',
		'photo_order_column' => 'Domyślna kolumna używana do sortowania zdjęć',
		'photo_order_direction' => 'Domyślna kolejność sortowania zdjęć',
		'album_order_column' => 'Domyślna kolumna używana do sortowania albumów',
		'album_order_direction' => 'Domyślna kolejność sortowania albumów',
		'aspect_ratio' => 'Domyślny współczynnik proporcji dla miniatur albumów',
		'photo_layout' => 'Układ dla zdjęć',
		'album_decoration' => 'Pokaż dekoracje na okładce albumu (liczba podalbumów i/lub zdjęć)',
		'album_decoration_direction' => 'Wyrównanie dekoracji albumu w poziomie lub w pionie',
		'photo_overlay' => 'Domyślne informacje o nakładce obrazu',
		'license_default' => 'Domyślna licencja używana dla albumów',
		'license_help' => 'Potrzebujesz pomocy w wyborze?',
	],
	'geolocation' => [
		'header' => 'Geolokalizacja',
		'map_display' => 'Wyświetlanie mapy z podanymi współrzędnymi GPS',
		'map_display_public' => 'Zezwalanie anonimowym użytkownikom na dostęp do mapy',
		'map_provider' => 'Definiuje dostawcę mapy',
		'map_include_subalbums' => 'Zawiera zdjęcia podrzędnych albumów na mapie',
		'location_decoding' => 'Korzystanie z dekodowania lokalizacji GPS',
		'location_show' => 'Pokaż lokalizację wyodrębnioną ze współrzędnych GPS',
		'location_show_public' => 'Anonimowi użytkownicy mogą uzyskać dostęp do lokalizacji wyodrębnionej ze współrzędnych GPS',
	],
	'cssjs' => [
		'header' => 'Custom CSS & Js',
		'change_css' => 'Zmiana CSS',
		'change_js' => 'Zmiana JS',
	],
	'all' => [
		'old_setting_style' => 'Stary styl ustawień',
		'expert_settings' => 'Expert Mode',
		'change_detected' => 'Niektóre ustawienia zostały zmienione.',
		'save' => 'Zapisz',
		'back_to_settings' => 'Back to grouped settings',
	],

	'tool_option' => [
		'disabled' => 'wyłączony',
		'enabled' => 'włączony',
		'discover' => 'odkryj',
	],

	'groups' => [
		'general' => 'General',
		'system' => 'System',
		'modules' => 'Modules',
		'advanced' => 'Advanced',
	],
];