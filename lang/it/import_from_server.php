<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Sincronizza i file del server',
    'description' => 'Sincronizza i file del tuo server con Lychee. Verranno importate le foto da una directory e da tutte le sue sottodirectory. Questo processo è molto lento: consigliamo di usare worker e code per evitare timeout.',
    'sync' => 'Sincronizza',
    'loading' => 'Caricamento...',
    'selected_directory' => 'Directory attualmente selezionata:',
    'resync_metadata' => 'Risincronizza i metadati dei file esistenti.',
    'delete_imported' => 'Elimina i file originali.',
    'import_via_symlink' => 'Importa le foto tramite collegamento simbolico invece di copiare i file.',
    'skip_duplicates' => 'Salta foto e album se sono già presenti nella galleria.',
    'delete_missing_photos' => 'Elimina le foto dell\'album non presenti nella directory sincronizzata.',
    'delete_missing_albums' => 'Elimina gli album dell\'album padre non presenti nella directory sincronizzata.',
    'importing_please_be_patient' => 'Importazione in corso, attendi...',
];

