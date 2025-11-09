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
];
