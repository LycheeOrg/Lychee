<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenção',
    'intro' => 'Nesta página encontrará as fotos duplicadas encontradas na sua base de dados.',
    'found' => ' duplicados encontrados!',
    'invalid-search' => ' Pelo menos a condição de checksum ou de título tem de estar assinalada.',
    'checksum-must-match' => 'O checksum tem de coincidir.',
    'title-must-match' => 'O título tem de coincidir.',
    'must-be-in-same-album' => 'Tem de estar no mesmo álbum.',
    'columns' => [
        'album' => 'Álbum',
        'photo' => 'Foto',
        'checksum' => 'Checksum',
    ],
    'warning' => [
        'no-original-left' => 'Não resta nenhum original.',
        'keep-one' => 'Selecionou todos os duplicados deste grupo. Escolha pelo menos um duplicado para manter.',
    ],
    'delete-selected' => 'Eliminar selecionados',
];
