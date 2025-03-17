<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Fix-tree Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Konserwacja',
	'intro' => 'Ta strona umożliwia ręczną zmianę kolejności i poprawianie albumów.<br />Przed dokonaniem jakichkolwiek modyfikacji zdecydowanie zalecamy zapoznanie się ze strukturami drzew zagnieżdżonych zestawów.',
	'warning' => 'Możesz naprawdę zepsuć swoją instalację Lychee, modyfikuj wartości na własne ryzyko.',

	'help' => [
		'header' => 'Pomoc',
		'hover' => 'Najedź kursorem na identyfikatory lub tytuły, aby podświetlić powiązane albumy.',
		'left' => '<span class="text-muted-color-emphasis font-bold">Lewa</span>',
		'right' => '<span class="text-muted-color-emphasis font-bold">W prawo</span>.',
		'convenience' => 'Dla wygody, przyciski <i class="pi pi-angle-up"></i> i <i class="pi pi-angle-down"></i> pozwalają na zmianę wartości %s i %s odpowiednio o +1 i -1 z propagacją.',
		'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> i <i class="text-warning-600 pi pi-chevron-circle-right" ></i> wskazuje, że wartość %s (i odpowiednio %s) jest gdzieś zduplikowana.',
		'parent-marked' => 'Oznaczenie <span class="font-bold text-danger-600">Parent Id</span> wskazuje, że %s i %s nie spełniają struktury drzewa Nest Set. Należy edytować wartości <span class="font-bold text-danger-600">Parent Id</span> lub %s/%s.',
		'slowness' => 'W przypadku dużej liczby albumów ta strona będzie działać wolno.',
	],

	'buttons' => [
		'reset' => 'Reset',
		'check' => 'Sprawdź',
		'apply' => 'Zastosuj',
	],

	'table' => [
		'title' => 'Tytuł',
		'left' => 'Lewa',
		'right' => 'Prawo',
		'id' => 'Id',
		'parent' => 'Identyfikator rodzica',
	],

	'errors' => [
		'invalid' => 'Nieprawidłowe drzewo!',
		'invalid_details' => 'Nie stosujemy tego rozwiązania, ponieważ gwarantuje ono uszkodzenie stanu.',
		'invalid_left' => 'Album %s ma nieprawidłową lewą wartość.',
		'invalid_right' => 'Album %s ma nieprawidłową prawą wartość.',
		'invalid_left_right' => 'Album %s ma nieprawidłowe wartości lewy/prawy. Lewa strona powinna być mniejsza niż prawa: %s < %s.',
		'duplicate_left' => 'Album %s ma zduplikowaną lewą wartość %s.',
		'duplicate_right' => 'Album %s ma zduplikowaną wartość prawą %s.',
		'parent' => 'Album %s ma nieoczekiwany identyfikator nadrzędny %s.',
		'unknown' => 'W albumie %s wystąpił nieznany błąd.',
	],
];