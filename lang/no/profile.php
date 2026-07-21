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
        'token_registered' => '%s-token registrert.',
        'setup' => 'Sett opp %s',
        'reset' => 'tilbakestill',
        'credential_deleted' => 'Legitimasjon slettet!',
    ],
    'u2f' => [
        'header' => 'Passnøkkel/MFA/2FA',
        'info' => 'Dette gir kun mulighet til å bruke WebAuthn for autentisering i stedet for brukernavn og passord.',
        'empty' => 'Listen over legitimasjon er tom!',
        'not_secure' => 'Miljøet er ikke sikret. U2F er ikke tilgjengelig.',
        'new' => 'Registrer ny enhet.',
        'credential_deleted' => 'Legitimasjon slettet!',
        'credential_updated' => 'Legitimasjon oppdatert!',
        'credential_registred' => 'Registrering vellykket!',
        '5_chars' => 'Minst 5 tegn.',
    ],
    'preferences' => [
        'header' => 'Innstillinger',
        'save' => 'Lagre innstilling',
        'reset' => 'Tilbakestill',
        'change_saved' => 'Innstilling lagret!',
    ],
    'shared_albums' => [
        'instruction' => 'Velg hvordan delte album (album fra andre brukere) vises i galleriet ditt:',
        'mode_default' => 'Bruk serverens standard',
        'mode_default_desc' => 'Arv serverens standard synlighetsmodus.',
        'mode_show' => 'Vis i linje',
        'mode_show_desc' => 'Delte album vises under dine egne album.',
        'mode_separate' => 'Separate faner',
        'mode_separate_desc' => 'Vis album i separate faner: «Mine album» og «Delt med meg».',
        'mode_separate_shared_only' => 'Kun delte',
        'mode_separate_shared_only_desc' => 'Separate faner som kun viser direkte delte album (offentlige album utelates).',
        'mode_hide' => 'Skjul',
        'mode_hide_desc' => 'Ikke vis delte album.',
    ],
];
