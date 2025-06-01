<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Mantenimiento',
	'intro' => 'En esta página encontrará las imágenes duplicadas encontradas en su base de datos.',
	'found' => ' ¡duplicados encontrados!',
	'invalid-search' => ' Al menos la condición de suma de verificación o título debe estar marcada.',
	'checksum-must-match' => 'La suma de verificación debe coincidir.',
	'title-must-match' => 'El título debe coincidir.',
	'must-be-in-same-album' => 'Debe estar en el mismo álbum.',

	'columns' => [
		'album' => 'Álbum',
		'photo' => 'Foto',
		'checksum' => 'Suma de verificación',
	],

	'warning' => [
		'no-original-left' => 'No queda ningún original.',
		'keep-one' => 'Ha seleccionado todos los duplicados en este grupo. Por favor, elija al menos un duplicado para conservar.',
	],

	'delete-selected' => 'Eliminar seleccionados',
];