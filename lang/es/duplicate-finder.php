<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Mantenimiento',
    'intro' => 'En esta página encontrará las imágenes duplicadas que se encuentran en su base de datos.',
    'found' => ' ¡Se han encontrado duplicados!',
    'invalid-search' => ' Como mínimo, se debe comprobar la suma de comprobación o el estado del título.',
    'checksum-must-match' => 'La suma de comprobación debe coincidir.',
    'title-must-match' => 'El título debe coincidir.',
    'must-be-in-same-album' => 'Debe estar en el mismo álbum.',
    'columns' => [
        'album' => 'Álbum',
        'photo' => 'Photo',
        'checksum' => 'Checksum',
    ],
    'warning' => [
        'no-original-left' => 'No original left.',
        'keep-one' => 'You selected all duplicates in this group. Please chose at least one duplicate to keep.',
    ],
    'delete-selected' => 'Delete selected',
];
