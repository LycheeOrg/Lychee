<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Compartir',
    'info' => 'Esta página ofrece una descripción general y la posibilidad de editar los derechos de uso compartido asociados a los álbumes.',
    'album_title' => 'Título del álbum',
    'username' => 'Nombre de usuario',
    'no_data' => 'La lista para compartir está vacía.',
    'share' => 'Compartir',
    'add_new_access_permission' => 'Añadir un nuevo permiso de acceso',
    'permission_deleted' => '¡Permiso eliminado!',
    'permission_created' => '¡Permiso creado!',
    'propagate' => 'Propagar',
    'propagate_help' => 'Propagar los permisos de acceso actuales a todos los descendientes (subálbumes y sus respectivos subálbumes, etc.)',
    'propagate_default' => 'Por defecto, los permisos existentes (álbum-usuario)<br>se actualizan y se añaden los que faltan.<br>Los permisos adicionales que no aparecen en esta lista no se modifican.',
    'propagate_overwrite' => 'Sobrescribir los permisos existentes en lugar de actualizarlos.<br>Esto también eliminará todos los permisos que no estén presentes en esta lista.',
    'propagate_warning' => 'Esta acción no se puede deshacer.',
    'permission_overwritten' => '¡Propagación realizada! ¡Permiso sobrescrito!',
    'permission_updated' => '¡Propagación correcta! ¡Permiso actualizado!',
    'bluk_share' => 'Compartir en bloque',
    'bulk_share_instr' => 'Seleccione varios álbumes y usuarios para compartir.',
    'albums' => 'Álbumes',
    'users' => 'Usuarios',
    'no_users' => 'No hay usuarios seleccionables.',
    'no_albums' => 'No hay álbumes seleccionables.',
    'grants' => [
        'read' => 'Otorga acceso de lectura',
        'original' => 'Otorga acceso a la fotografía original',
        'download' => 'Descarga de subvenciones',
        'upload' => 'Subir permisos',
        'edit' => 'Editar permisos',
        'delete' => 'Eliminar permisos',
    ],
];
