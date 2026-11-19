<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Sincroniza los archivos de tu servidor',
    'description' => 'Sincroniza los archivos de tu servidor con Lychee. Esto importará las fotos de un directorio y de todos sus subdirectorios. Este proceso es muy lento y recomendamos usar workers y colas para evitar que se agote el tiempo de espera.',
    'sync' => 'Sincronizar',
    'loading' => 'Cargando...',
    'selected_directory' => 'Directorio seleccionado actualmente:',
    'resync_metadata' => 'Volver a sincronizar los metadatos de los archivos existentes.',
    'delete_imported' => 'Eliminar los archivos originales.',
    'import_via_symlink' => 'Importar las fotos mediante enlaces simbólicos en lugar de copiar los archivos.',
    'skip_duplicates' => 'Omitir fotos y álbumes si ya existen en la galería.',
    'delete_missing_photos' => 'Eliminar las fotos del álbum que no estén presentes en el directorio sincronizado.',
    'delete_missing_albums' => 'Eliminar los álbumes del álbum principal que no estén presentes en el directorio sincronizado.',
    'importing_please_be_patient' => 'Importando, ten paciencia...',
];

