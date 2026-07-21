<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Vedlikehold',
    'intro' => 'På denne siden finner du duplikatbildene som er funnet i databasen din.',
    'found' => ' duplikater funnet!',
    'invalid-search' => ' Minst betingelsen for sjekksum eller tittel må være valgt.',
    'checksum-must-match' => 'Sjekksum må samsvare.',
    'title-must-match' => 'Tittel må samsvare.',
    'must-be-in-same-album' => 'Må være i samme album.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Bilde',
        'checksum' => 'Sjekksum',
    ],
    'warning' => [
        'no-original-left' => 'Ingen original igjen.',
        'keep-one' => 'Du har valgt alle duplikater i denne gruppen. Velg minst ett duplikat å beholde.',
    ],
    'delete-selected' => 'Slett valgte',
];
