<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duplicate Finder Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Údržba',
    'intro' => 'Na tejto stránke nájdete duplicitné obrázky nájdené vo vašej databáze.',
    'found' => ' nájdených duplikátov!',
    'invalid-search' => ' Musí byť zaškrtnutá aspoň podmienka kontrolného súčtu alebo názvu.',
    'checksum-must-match' => 'Kontrolný súčet sa musí zhodovať.',
    'title-must-match' => 'Názov sa musí zhodovať.',
    'must-be-in-same-album' => 'Musia byť v rovnakom albume.',
    'columns' => [
        'album' => 'Album',
        'photo' => 'Fotografia',
        'checksum' => 'Kontrolný súčet',
    ],
    'warning' => [
        'no-original-left' => 'Nezostal žiadny originál.',
        'keep-one' => 'V tejto skupine ste vybrali všetky duplikáty. Vyberte aspoň jeden duplikát, ktorý sa ponechá.',
    ],
    'delete-selected' => 'Odstrániť vybrané',
];
