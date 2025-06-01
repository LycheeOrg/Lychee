<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Users page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Usuarios',
	'description' => 'Aquí puede gestionar los usuarios de su instalación de Lychee. Puede crear, editar y eliminar usuarios.',
	'create' => 'Crear un nuevo usuario',
	'username' => 'Nombre de usuario',
	'password' => 'Contraseña',
	'legend' => 'Leyenda',
	'upload_rights' => 'Cuando está seleccionado, el usuario puede subir contenido.',
	'edit_rights' => 'Cuando está seleccionado, el usuario puede modificar su perfil (nombre de usuario, contraseña).',
	'quota' => 'Cuando se establece, el usuario tiene una cuota de espacio para imágenes (en kB).',

	'user_deleted' => 'Usuario eliminado',
	'user_created' => 'Usuario creado',
	'user_updated' => 'Usuario actualizado',
	'change_saved' => '¡Cambio guardado!',

	'create_edit' => [
		'upload_rights' => 'El usuario puede subir contenido.',
		'edit_rights' => 'El usuario puede modificar su perfil (nombre de usuario, contraseña).',
		'admin_rights' => 'User has admin rights.',
		'quota' => 'El usuario tiene un límite de cuota.',
		'quota_kb' => 'cuota en kB (0 por defecto)',
		'note' => 'Nota del administrador (no visible públicamente)',
		'create' => 'Crear',
		'edit' => 'Editar',
	],
	'invite' => [
		'button' => 'Invite user',
		'links_are_not_revokable' => 'Invitation links are not revokable.',
		'link_is_valid_x_days' => 'This link is valid for %d days.',
	],
	'line' => [
		'owner' => 'Owner',
		'admin' => 'usuario administrador',
		'edit' => 'Editar',
		'delete' => 'Eliminar',
	],
];