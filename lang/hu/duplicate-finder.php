<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Karbantartás',
    'intro' => 'Ezen az oldalon az adatbázisban talált duplikált képeket láthatja.',
    'found' => ' duplikátum található!',
    'invalid-search' => ' Legalább az ellenőrzőösszeg vagy a cím feltételét be kell jelölni.',
    'checksum-must-match' => 'Az ellenőrzőösszegnek egyeznie kell.',
    'title-must-match' => 'A címnek egyeznie kell.',
    'must-be-in-same-album' => 'Ugyanabban az albumban kell lennie.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Fotó',
        'checksum' => 'Ellenőrzőösszeg',
    ],
    'warning' => [
        'no-original-left' => 'Nem maradt eredeti példány.',
        'keep-one' => 'Ebben a csoportban az összes duplikátumot kijelölte. Kérjük, válasszon ki legalább egy megtartandó duplikátumot.',
    ],
    'delete-selected' => 'Kijelöltek törlése',
];
