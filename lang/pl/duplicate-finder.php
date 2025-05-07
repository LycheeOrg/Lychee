<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Konserwacja',
	'intro' => 'Na tej stronie znajdują się zduplikowane obrazy znalezione w bazie danych.',
	'found' => ' Znaleziono duplikaty!',
	'invalid-search' => ' Należy sprawdzić przynajmniej sumę kontrolną lub warunek tytułu.',
	'checksum-must-match' => 'Suma kontrolna musi się zgadzać.',
	'title-must-match' => 'Tytuł musi się zgadzać.',
	'must-be-in-same-album' => 'Muszą być w tym samym albumie.',

	'columns' => [
		'album' => 'Album',
		'photo' => 'Zdjęcie',
		'checksum' => 'Suma kontrolna',
	],

	'warning' => [
		'no-original-left' => 'Nie pozostał żaden oryginał.',
		'keep-one' => 'Wybrano wszystkie duplikaty w tej grupie. Wybierz co najmniej jeden duplikat do zachowania.',
	],

	'delete-selected' => 'Usuń zaznaczone',
];