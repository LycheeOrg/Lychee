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
        'photo' => 'Foto',
        'checksum' => 'Suma de comprobación',
    ],
    'warning' => [
        'no-original-left' => 'No queda ningún original.',
        'keep-one' => 'Has seleccionado todos los duplicados de este grupo. Por favor, elige al menos un duplicado para conservar.',
    ],
    'delete-selected' => 'Eliminar seleccionados',
];
