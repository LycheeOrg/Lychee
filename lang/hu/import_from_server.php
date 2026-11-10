<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Szerverfájlok szinkronizálása',
    'description' => 'Szinkronizálja a szerveren lévő fájlokat a Lychee-vel. Ez egy könyvtárból és annak összes alkönyvtárából importálja a fotókat. A folyamat nagyon lassú, ezért az időtúllépés elkerülése érdekében workerek és várólisták használatát javasoljuk.',
    'sync' => 'Szinkronizálás',
    'loading' => 'Betöltés...',
    'selected_directory' => 'Jelenleg kiválasztott könyvtár:',
    'resync_metadata' => 'A meglévő fájlok metaadatainak újraszinkronizálása.',
    'delete_imported' => 'Az eredeti fájlok törlése.',
    'import_via_symlink' => 'Fotók importálása szimbolikus hivatkozással a fájlok másolása helyett.',
    'skip_duplicates' => 'Fotók és albumok kihagyása, ha már léteznek a galériában.',
    'delete_missing_photos' => 'Az albumban lévő azon fotók törlése, amelyek nincsenek a szinkronizált könyvtárban.',
    'delete_missing_albums' => 'A szülőalbumban lévő azon albumok törlése, amelyek nincsenek a szinkronizált könyvtárban.',
    'importing_please_be_patient' => 'Importálás folyamatban, kérjük, legyen türelemmel...',
];

