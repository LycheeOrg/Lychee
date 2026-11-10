<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Underhåll',
    'intro' => 'På denna sida hittar du dubblettbilderna som hittats i din databas.',
    'found' => ' dubbletter hittades!',
    'invalid-search' => ' Minst villkoret för kontrollsumma eller titel måste vara markerat.',
    'checksum-must-match' => 'Kontrollsumman måste matcha.',
    'title-must-match' => 'Titeln måste matcha.',
    'must-be-in-same-album' => 'Måste ligga i samma album.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Foto',
        'checksum' => 'Kontrollsumma',
    ],
    'warning' => [
        'no-original-left' => 'Inget original kvar.',
        'keep-one' => 'Du har markerat alla dubbletter i denna grupp. Välj minst en dubblett att behålla.',
    ],
    'delete-selected' => 'Radera markerade',
];
