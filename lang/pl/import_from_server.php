<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Synchronizuj pliki serwera',
    'description' => 'Synchronizuj pliki serwera z Lychee. Spowoduje to zaimportowanie zdjęć z katalogu i wszystkich jego podkatalogów. Ten proces jest bardzo wolny, dlatego zalecamy korzystanie z workerów i kolejek, aby uniknąć przekroczenia limitu czasu.',
    'sync' => 'Synchronizuj',
    'loading' => 'Ładowanie...',
    'selected_directory' => 'Aktualnie wybrany katalog:',
    'resync_metadata' => 'Ponownie synchronizuj metadane istniejących plików.',
    'delete_imported' => 'Usuń oryginalne pliki.',
    'import_via_symlink' => 'Importuj zdjęcia przez dowiązanie symboliczne zamiast kopiowania plików.',
    'skip_duplicates' => 'Pomiń zdjęcia i albumy, jeśli już istnieją w galerii.',
    'delete_missing_photos' => 'Usuń z albumu zdjęcia, których nie ma w synchronizowanym katalogu.',
    'delete_missing_albums' => 'Usuń z albumu nadrzędnego albumy, których nie ma w synchronizowanym katalogu.',
    'importing_please_be_patient' => 'Importowanie, prosimy o cierpliwość...',
];

