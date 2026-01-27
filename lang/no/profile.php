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
        'enter_current_password' => 'Skriv inn ditt nåværende passord:',
        'current_password' => 'Nåværende passord',
        'credentials_update' => 'Dine innloggingsdetalier din vil bli endret til følgende:',
        'username' => 'Brukernavn',
        'new_password' => 'Nytt passord',
        'confirm_new_password' => 'Bekreft nytt passord',
        'email_instruction' => 'Legg til e-postadressen din nedenfor for å aktivere mottak av e-postvarsler. For å slutte å motta e-poster, fjern ganske enkelt e-postadressen din nedenfor.',
        'email' => 'Epostadresse',
        'change' => 'Endre pålogging',
        'api_token' => 'API-token …',
        'missing_fields' => 'Manglende felt',
        'ldap_managed' => 'Brukerens påloggingsinformasjon administreres av LDAP.',
    ],
    'register' => [
        'username_exists' => 'Brukernavnet finnes allerede.',
        'password_mismatch' => 'Passordene stemmer ikke overens.',
        'signup' => 'Registrer deg',
        'error' => 'Det oppsto en feil under registrering av kontoen din.',
        'success' => 'Kontoen din er opprettet.',
    ],
    'token' => [
        'unavailable' => 'Du har allerede sett denne tokenen.',
        'no_data' => 'Ingen token-API er generert.',
        'disable' => 'Deaktiver',
        'disabled' => 'Token deaktivert',
        'warning' => 'Denne tokenen vil ikke vises igjen. Kopier den og oppbevar den på et trygt sted.',
        'reset' => 'Tilbakestill tokenet',
        'create' => 'Opprett et nytt token',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth er ikke tilgjengelig',
        'setup_env' => 'Konfigurer legitimasjonen i .env-filen din',
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
