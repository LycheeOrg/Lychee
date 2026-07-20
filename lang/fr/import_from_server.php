<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Synchroniser vos fichiers serveur',
    'description' => 'Synchronisez vos fichiers serveur avec Lychee. Cela importera les photos d’un répertoire ainsi que de tous ses sous-répertoires. Ce processus est très lent et nous recommandons d’utiliser des workers et des files d’attente afin d’éviter les délais d’expiration.',
    'sync' => 'Synchroniser',
    'loading' => 'Chargement...',
    'selected_directory' => 'Répertoire actuellement sélectionné :',
    'resync_metadata' => 'Resynchroniser les métadonnées des fichiers existants.',
    'delete_imported' => 'Supprimer les fichiers d’origine.',
    'import_via_symlink' => 'Importer les photos via un lien symbolique plutôt qu’en copiant les fichiers.',
    'skip_duplicates' => 'Ignorer les photos et albums s’ils existent déjà dans la galerie.',
    'delete_missing_photos' => 'Supprimer les photos de l’album qui ne sont pas présentes dans le répertoire synchronisé.',
    'delete_missing_albums' => 'Supprimer les albums de l’album parent qui ne sont pas présents dans le répertoire synchronisé.',
    'importing_please_be_patient' => 'Importation en cours, veuillez patienter...',
];

