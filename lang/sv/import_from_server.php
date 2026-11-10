<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Synkronisera dina serverfiler',
    'description' => 'Synkronisera dina serverfiler med Lychee. Detta importerar foton från en katalog och alla dess underkataloger. Processen är mycket långsam och vi rekommenderar att använda workers och köer för att undvika timeout.',
    'sync' => 'Synkronisera',
    'loading' => 'Laddar...',
    'selected_directory' => 'Nuvarande vald katalog:',
    'resync_metadata' => 'Synkronisera om metadata för befintliga filer.',
    'delete_imported' => 'Radera originalfilerna.',
    'import_via_symlink' => 'Importera foton via symbolisk länk i stället för att kopiera filerna.',
    'skip_duplicates' => 'Hoppa över foton och album som redan finns i galleriet.',
    'delete_missing_photos' => 'Radera foton i albumet som inte finns i den synkroniserade katalogen.',
    'delete_missing_albums' => 'Radera album i det överordnade albumet som inte finns i den synkroniserade katalogen.',
    'importing_please_be_patient' => 'Importerar, ha tålamod...',
];

