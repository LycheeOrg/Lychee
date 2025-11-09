<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Onderhoud',
    'intro' => 'Op deze pagina vindt u de dubbele foto’s die in uw database zijn gevonden.',
    'found' => ' duplicaten gevonden!',
    'invalid-search' => ' Minstens de checksum- of titelvoorwaarde moet zijn aangevinkt.',
    'checksum-must-match' => 'Checksum moet overeenkomen.',
    'title-must-match' => 'Titel moet overeenkomen.',
    'must-be-in-same-album' => 'Moet in hetzelfde album zijn.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Foto',
        'checksum' => 'Checksum',
    ],
    'warning' => [
        'no-original-left' => 'Geen origineel meer over.',
        'keep-one' => 'U heeft alle duplicaten in deze groep geselecteerd. Kies alstublieft minstens één duplicaat om te behouden.',
    ],
    'delete-selected' => 'Geselecteerde verwijderen',
];
