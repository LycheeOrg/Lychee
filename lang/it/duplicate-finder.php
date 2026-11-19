<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenzione',
    'intro' => 'In questa pagina trovi le immagini duplicate presenti nel tuo database.',
    'found' => ' duplicati trovati!',
    'invalid-search' => ' Almeno una condizione tra checksum e titolo deve essere selezionata.',
    'checksum-must-match' => 'Il checksum deve corrispondere.',
    'title-must-match' => 'Il titolo deve corrispondere.',
    'must-be-in-same-album' => 'Devono essere nello stesso album.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Foto',
        'checksum' => 'Checksum',
    ],
    'warning' => [
        'no-original-left' => 'Nessun originale rimasto.',
        'keep-one' => 'Hai selezionato tutti i duplicati di questo gruppo. Scegline almeno uno da conservare.',
    ],
    'delete-selected' => 'Elimina selezionati',
];
