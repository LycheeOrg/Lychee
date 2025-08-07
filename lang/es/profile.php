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
        'enter_current_password' => 'Introduzca su contraseña actual:',
        'current_password' => 'Contraseña actual',
        'credentials_update' => 'Sus credenciales se cambiarán a lo siguiente:',
        'username' => 'Nombre de usuario',
        'new_password' => 'Nueva contraseña',
        'confirm_new_password' => 'Confirmar nueva contraseña',
        'email_instruction' => 'Add your email below to enable receiving email notifications. To stop receiving emails, simply remove your email below.',
        'email' => 'Email',
        'change' => 'Change Login',
        'api_token' => 'API Token ...',
        'missing_fields' => 'Missing fields',
    ],
    'register' => [
        'username_exists' => 'Username already exists.',
        'password_mismatch' => 'The passwords do not match.',
        'signup' => 'Sign Up',
        'error' => 'An error occurred while registering your account.',
        'success' => 'Your account has been successfully created.',
    ],
    'token' => [
        'unavailable' => 'You have already viewed this token.',
        'no_data' => 'No token API have been generated.',
        'disable' => 'Disable',
        'disabled' => 'Token disabled',
        'warning' => 'This token will not be displayed again. Copy it and keep it in a safe place.',
        'reset' => 'Reset the token',
        'create' => 'Create a new token',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth is not available',
        'setup_env' => 'Set up the credentials in your .env',
        'token_registered' => '%s token registered.',
        'setup' => 'Set up %s',
        'reset' => 'reset',
        'credential_deleted' => 'Credential deleted!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'This only provides the ability to use WebAuthn to authenticate instead of username & password.',
        'empty' => 'Credentials list is empty!',
        'not_secure' => 'Environment not secured. U2F not available.',
        'new' => 'Register new device.',
        'credential_deleted' => 'Credential deleted!',
        'credential_updated' => 'Credential updated!',
        'credential_registred' => 'Registration successful!',
        '5_chars' => 'At least 5 chars.',
    ],
];
