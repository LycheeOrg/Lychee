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
        'enter_current_password' => 'Wprowadź swoje aktualne hasło',
        'current_password' => 'Aktualne hasło',
        'credentials_update' => 'Twoje poświadczenia zostaną zmienione na następujące:',
        'username' => 'Nazwa użytkownika',
        'new_password' => 'Nowe hasło',
        'confirm_new_password' => 'Potwierdź nowe hasło',
        'email_instruction' => 'Dodaj swój adres e-mail poniżej, aby włączyć otrzymywanie powiadomień e-mail. Aby przestać otrzymywać wiadomości e-mail, po prostu usuń swój adres e-mail poniżej.',
        'email' => 'E-mail',
        'change' => 'Zmień login',
        'api_token' => 'Token API ...',
        'missing_fields' => 'Brakujące pola',
        'ldap_managed' => 'Informacje logowania użytkownika są zarządzane przez LDAP.',
    ],
    'register' => [
        'username_exists' => 'Nazwa użytkownika już istnieje.',
        'password_mismatch' => 'Hasła nie pasują do siebie.',
        'signup' => 'Zarejestruj się',
        'error' => 'Wystąpił błąd podczas rejestrowania konta.',
        'success' => 'Twoje konto zostało pomyślnie utworzone.',
    ],
    'token' => [
        'unavailable' => 'Ten token został już wyświetlony.',
        'no_data' => 'Nie wygenerowano tokenu API.',
        'disable' => 'Wyłącz',
        'disabled' => 'Token wyłączony',
        'warning' => 'Ten token nie będzie wyświetlany ponownie. Należy go skopiować i przechowywać w bezpiecznym miejscu.',
        'reset' => 'Zresetuj token',
        'create' => 'Utwórz nowy token',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth nie jest dostępny',
        'setup_env' => 'Skonfiguruj poświadczenia w pliku .env',
        'token_registered' => 'Zarejestrowano token %s.',
        'setup' => 'Konfiguracja %s',
        'reset' => 'reset',
        'credential_deleted' => 'Poświadczenie usunięte!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'Zapewnia to jedynie możliwość używania WebAuthn do uwierzytelniania zamiast nazwy użytkownika i hasła.',
        'empty' => 'Lista poświadczeń jest pusta!',
        'not_secure' => 'Środowisko nie jest zabezpieczone. U2F nie jest dostępne.',
        'new' => 'Zarejestruj nowe urządzenie.',
        'credential_deleted' => 'Poświadczenie usunięte!',
        'credential_updated' => 'Poświadczenie zaktualizowane!',
        'credential_registred' => 'Rejestracja zakończona sukcesem!',
        '5_chars' => 'Co najmniej 5 znaków.',
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
