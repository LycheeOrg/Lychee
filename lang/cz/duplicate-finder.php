<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Údržba',
	'intro' => 'Na téhle stránce najdete duplikáty fotek nalezené ve vaší databázi',
	'found' => ' halezeno duplikátů!',
	'invalid-search' => ' Je nutné zadat alespoň kontrolní součet nebo název.',
	'checksum-must-match' => 'Kontrolní součet se musí shodovat.',
	'title-must-match' => 'Název se musí shodovat.',
	'must-be-in-same-album' => 'Musí být ve stejném albu.',
	'columns' => [
		'album' => 'Album',
		'photo' => 'Fotografie',
		'checksum' => 'Kontrolní součet',
	],
	'warning' => [
		'no-original-left' => 'Žádný originál nezůstal.',
		'keep-one' => 'Vybrali jste všechny duplikáty v této skupině. Vyberte prosím alespoň jednu duplikát, který chcete ponechat.',
	],
	'delete-selected' => 'Odstranit vybrané',
];
