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
    'permission_created' => 'Permission created!',
    'propagate' => 'Propagate',
    'propagate_help' => 'Propagate the current access permissions to all descendants<br>(sub-albums and their respective sub-albums etc)',
    'propagate_default' => 'By default, existing permissions (album-user)<br>are updated and the missing ones added.<br>Additional permissions not present in this list are left untouched.',
    'propagate_overwrite' => 'Overwrite the existing permissions instead of updating.<br>This will also remove all permissions not present in this list.',
    'propagate_warning' => 'This action cannot be undone.',
    'permission_overwritten' => 'Propagation successful! Permission overwritten!',
    'permission_updated' => 'Propagation successful! Permission updated!',
    'bluk_share' => 'Bulk share',
    'bulk_share_instr' => 'Select multiple albums and users to share with.',
    'albums' => 'Álbumes',
    'users' => 'Users',
    'no_users' => 'No selectable users.',
    'no_albums' => 'No selectable albums.',
    'grants' => [
        'read' => 'Grants read access',
        'original' => 'Grants access to original photo',
        'download' => 'Grants download',
        'upload' => 'Grants upload',
        'edit' => 'Grants edit',
        'delete' => 'Grants delete',
    ],
];
