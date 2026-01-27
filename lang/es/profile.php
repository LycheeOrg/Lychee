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
        'email_instruction' => 'Añade tu correo electrónico a continuación para recibir notificaciones por correo electrónico. Para dejar de recibir correos electrónicos, simplemente elimina tu correo electrónico a continuación.',
        'email' => 'Email',
        'change' => 'Cambiar inicio de sesión',
        'api_token' => 'Token API …',
        'missing_fields' => 'Campos faltantes',
        'ldap_managed' => 'La información de inicio de sesión del usuario está gestionada por LDAP.',
    ],
    'register' => [
        'username_exists' => 'El nombre de usuario ya existe.',
        'password_mismatch' => 'Las contraseñas no coinciden.',
        'signup' => 'Registrarse',
        'error' => 'Se ha producido un error al registrar su cuenta.',
        'success' => 'Su cuenta ha sido creada exitosamente.',
    ],
    'token' => [
        'unavailable' => 'Ya has visto este token.',
        'no_data' => 'No se ha generado ninguna API de token.',
        'disable' => 'Desactivar',
        'disabled' => 'Token deshabilitado',
        'warning' => 'Este token no se volverá a mostrar. Cópielo y guárdelo en un lugar seguro.',
        'reset' => 'Restablecer el token',
        'create' => 'Crear un nuevo token',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth no está disponible',
        'setup_env' => 'Configura las credenciales en tu .env',
        'token_registered' => 'Token% s registrado.',
        'setup' => 'Configurar %s',
        'reset' => 'reiniciar',
        'credential_deleted' => '¡Credencial eliminada!',
    ],
    'u2f' => [
        'header' => 'Clave de acceso/MFA/2FA',
        'info' => 'Esto solo proporciona la posibilidad de utilizar WebAuthn para autenticarse en lugar de nombre de usuario y contraseña.',
        'empty' => '¡La lista de credenciales está vacía!',
        'not_secure' => 'Entorno no asegurado. U2F no disponible.',
        'new' => 'Registrar nuevo dispositivo.',
        'credential_deleted' => '¡Credencial eliminada!',
        'credential_updated' => '¡Credencial actualizada!',
        'credential_registred' => '¡Registro exitoso!',
        '5_chars' => 'Al menos 5 caracteres.',
    ],
    'preferences' => [
        'header' => 'Preferences',
        'save' => 'Save Preference',
        'reset' => 'Reset',
        'change_saved' => 'Preference saved!',
    ],
    'shared_albums' => [
        'instruction' => 'Choose how shared albums (albums from other users) appear in your gallery:',
        'mode_default' => 'Use Server Default',
        'mode_default_desc' => 'Inherit the server\'s default visibility mode.',
        'mode_show' => 'Show Inline',
        'mode_show_desc' => 'Shared albums appear below your own albums.',
        'mode_separate' => 'Separate Tabs',
        'mode_separate_desc' => 'View albums in separate "My Albums" and "Shared with Me" tabs.',
        'mode_separate_shared_only' => 'Shared Only',
        'mode_separate_shared_only_desc' => 'Separate tabs showing only directly shared albums (excludes public albums).',
        'mode_hide' => 'Hide',
        'mode_hide_desc' => 'Don\'t show any shared albums.',
    ],
];
