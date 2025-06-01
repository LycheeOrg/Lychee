<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Profile page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Perfil',

	'login' => [
		'header' => 'Perfil',
		'enter_current_password' => 'Ingrese su contraseña actual:',
		'current_password' => 'Contraseña actual',
		'credentials_update' => 'Sus credenciales se cambiarán a lo siguiente:',
		'username' => 'Nombre de usuario',
		'new_password' => 'Nueva contraseña',
		'confirm_new_password' => 'Confirmar nueva contraseña',
		'email_instruction' => 'Agregue su correo electrónico a continuación para habilitar la recepción de notificaciones por correo electrónico. Para dejar de recibir correos electrónicos, simplemente elimine su correo electrónico a continuación.',
		'email' => 'Correo electrónico',
		'change' => 'Cambiar inicio de sesión',
		'api_token' => 'Token de API …',

		'missing_fields' => 'Campos faltantes',
	],

	'register' => [
		'username_exists' => 'Username already exists.',
		'password_mismatch' => 'The passwords do not match.',
		'signup' => 'Sign Up',
		'error' => 'An error occurred while registering your account.',
		'success' => 'Your account has been successfully created.',
	],

	'token' => [
		'unavailable' => 'Ya ha visto este token.',
		'no_data' => 'No se han generado tokens de API.',
		'disable' => 'Deshabilitar',
		'disabled' => 'Token deshabilitado',
		'warning' => 'Este token no se mostrará nuevamente. Cópielo y guárdelo en un lugar seguro.',
		'reset' => 'Restablecer el token',
		'create' => 'Crear un nuevo token',
	],

	'oauth' => [
		'header' => 'OAuth',
		'header_not_available' => 'OAuth no está disponible',
		'setup_env' => 'Configure las credenciales en su .env',
		'token_registered' => 'Token %s registrado.',
		'setup' => 'Configurar %s',
		'reset' => 'restablecer',
		'credential_deleted' => '¡Credencial eliminada!',
	],

	'u2f' => [
		'header' => 'Clave de acceso/MFA/2FA',
		'info' => 'Esto solo proporciona la capacidad de usar WebAuthn para autenticar en lugar de nombre de usuario y contraseña.',
		'empty' => '¡La lista de credenciales está vacía!',
		'not_secure' => 'Entorno no seguro. U2F no disponible.',
		'new' => 'Registrar nuevo dispositivo.',
		'credential_deleted' => '¡Credencial eliminada!',
		'credential_updated' => '¡Credencial actualizada!',
		'credential_registred' => '¡Registro exitoso!',
		'5_chars' => 'Al menos 5 caracteres.',
	],
];