<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import from Server
    |--------------------------------------------------------------------------
    */
    'title' => 'Sincronizar os ficheiros do servidor',
    'description' => 'Sincronize os ficheiros do seu servidor com o Lychee. Isto irá importar fotos de um diretório e de todos os seus subdiretórios. Este processo é muito lento e recomendamos a utilização de workers e filas para evitar timeouts.',
    'sync' => 'Sincronizar',
    'loading' => 'A carregar...',
    'selected_directory' => 'Diretório atualmente selecionado:',
    'resync_metadata' => 'Ressincronizar os metadados dos ficheiros existentes.',
    'delete_imported' => 'Eliminar os ficheiros originais.',
    'import_via_symlink' => 'Importar fotos através de ligação simbólica em vez de copiar os ficheiros.',
    'skip_duplicates' => 'Ignorar fotos e álbuns que já existam na galeria.',
    'delete_missing_photos' => 'Eliminar as fotos do álbum que não estejam presentes no diretório sincronizado.',
    'delete_missing_albums' => 'Eliminar os álbuns do álbum pai que não estejam presentes no diretório sincronizado.',
    'importing_please_be_patient' => 'A importar, por favor aguarde...',
];

