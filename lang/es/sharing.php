<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Sharing page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Compartir',

	'info' => 'Esta página proporciona una visión general y la capacidad de editar los derechos de uso compartido asociados con los álbumes.',
	'album_title' => 'Título del álbum',
	'username' => 'Nombre de usuario',
	'no_data' => 'La lista de compartidos está vacía.',
	'share' => 'Compartir',
	'add_new_access_permission' => 'Agregar un nuevo permiso de acceso',
	'permission_deleted' => '¡Permiso eliminado!',
	'permission_created' => '¡Permiso creado!',
	'propagate' => 'Propagar',

	'propagate_help' => 'Propagar los permisos de acceso actuales a todos los descendientes<br>(sub-álbumes y sus respectivos sub-álbumes, etc.)',
	'propagate_default' => 'Por defecto, los permisos existentes (álbum-usuario)<br>se actualizan y se agregan los que faltan.<br>Los permisos adicionales que no están presentes en esta lista no se modifican.',
	'propagate_overwrite' => 'Sobrescribir los permisos existentes en lugar de actualizarlos.<br>Esto también eliminará todos los permisos que no estén presentes en esta lista.',
	'propagate_warning' => 'Esta acción no se puede deshacer.',

	'permission_overwritten' => '¡Propagación exitosa! ¡Permiso sobrescrito!',
	'permission_updated' => '¡Propagación exitosa! ¡Permiso actualizado!',
	'bluk_share' => 'Compartir en masa',
	'bulk_share_instr' => 'Seleccione múltiples álbumes y usuarios con los que compartir.',
	'albums' => 'Álbumes',
	'users' => 'Usuarios',
	'no_users' => 'No hay usuarios seleccionables.',
	'no_albums' => 'No hay álbumes seleccionables.',

	'grants' => [
		'read' => 'Concede acceso de lectura',
		'original' => 'Concede acceso a la foto original',
		'download' => 'Concede descarga',
		'upload' => 'Concede subida',
		'edit' => 'Concede edición',
		'delete' => 'Concede eliminación',
	],
];