<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    */
    'title' => 'Profil',
    'login' => [
        'header' => 'Profil',
        'enter_current_password' => 'Geben Sie Ihr aktuelles Passwort ein:',
        'current_password' => 'Aktuelles Passwort',
        'credentials_update' => 'Ihre Anmeldedaten werden wie folgt geändert:',
        'username' => 'Benutzername',
        'new_password' => 'Neues Passwort',
        'confirm_new_password' => 'Neues Passwort bestätigen',
        'email_instruction' => 'Fügen Sie unten Ihre E-Mail-Adresse ein, um E-Mail-Benachrichtigungen zu erhalten. Wenn Sie keine E-Mails mehr erhalten möchten, entfernen Sie Ihre E-Mail-Adresse einfach unten.',
        'email' => 'E-Mail',
        'change' => 'Anmeldung ändern',
        'api_token' => 'API Token …',
        'missing_fields' => 'Fehlende Felder',
        'ldap_managed' => 'Benutzer-Anmeldeinformationen werden über LDAP verwaltet.',
    ],
    'register' => [
        'username_exists' => 'Benutzername existiert bereits.',
        'password_mismatch' => 'Die Passwörter stimmen nicht überein.',
        'signup' => 'Registrieren',
        'error' => 'Bei der Registrierung Ihres Kontos ist ein Fehler aufgetreten.',
        'success' => 'Ihr Konto wurde erfolgreich erstellt.',
    ],
    'token' => [
        'unavailable' => 'Sie haben diesen Token bereits gesehen.',
        'no_data' => 'Es wurde kein API-Token erzeugt.',
        'disable' => 'Deaktiviere',
        'disabled' => 'Token deaktiviert',
        'warning' => 'Dieses Token wird nicht nochmal angezeigt. Kopieren Sie ihn und bewahren Sie ihn an einem sicheren Ort auf.',
        'reset' => 'Token zurücksetzen',
        'create' => 'Einen neuen Sicherheitsschlüssel erstellen',
    ],
    'oauth' => [
        'header' => 'Authentifizierung',
        'header_not_available' => 'OAuth ist nicht verfügbar',
        'setup_env' => 'Richten Sie die Anmeldedaten in Ihrer .env ein',
        'token_registered' => '%s Token registriert.',
        'setup' => '%s einrichten',
        'reset' => 'Zurücksetzen',
        'credential_deleted' => 'Anmeldedaten gelöscht!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'Dies ermöglicht lediglich die Verwendung von WebAuthn zur Authentifizierung anstelle von Benutzername und Passwort.',
        'empty' => 'Die Liste der Anmeldedaten ist leer!',
        'not_secure' => 'Die Umgebung ist nicht gesichert. U2F nicht verfügbar.',
        'new' => 'Neues Gerät registrieren.',
        'credential_deleted' => 'Anmeldedaten gelöscht!',
        'credential_updated' => 'Anmeldedaten aktualisiert!',
        'credential_registred' => 'Anmeldung erfolgreich!',
        '5_chars' => 'Mindestens 5 Zeichen.',
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
