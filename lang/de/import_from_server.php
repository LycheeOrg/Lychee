<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Server-Dateien synchronisieren',
    'description' => 'Synchronisieren Sie Ihre Server-Dateien mit Lychee. Dies importiert Fotos aus einem Verzeichnis und allen Unterverzeichnissen. Dieser Vorgang ist sehr langsam; wir empfehlen die Nutzung von Workern und Queues (Warteschlangen), um Zeitüberschreitungen (Timeouts) zu vermeiden.',
    'sync' => 'Synchronisieren',
    'loading' => 'Lade …',
    'selected_directory' => 'Aktuell ausgewähltes Verzeichnis:',
    'resync_metadata' => 'Metadaten bestehender Dateien erneut synchronisieren.',
    'delete_imported' => 'Originaldateien nach dem Import löschen.',
    'import_via_symlink' => 'Fotos via Symlink importieren (statt die Dateien zu kopieren).',
    'skip_duplicates' => 'Fotos und Alben überspringen, wenn sie bereits in der Galerie existieren.',
    'delete_missing_photos' => 'Fotos im Album löschen, die im synchronisierten Verzeichnis nicht vorhanden sind.',
    'delete_missing_albums' => 'Alben im übergeordneten Album löschen, die im synchronisierten Verzeichnis nicht vorhanden sind.',
    'importing_please_be_patient' => 'Import läuft, bitte haben Sie etwas Geduld …',
];

