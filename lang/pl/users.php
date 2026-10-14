<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Użytkownicy',
    'description' => 'Tutaj możesz zarządzać użytkownikami swojej instalacji Lychee. Możesz tworzyć, edytować i usuwać użytkowników.',
    'create' => 'Tworzenie nowego użytkownika',
    'username' => 'Nazwa użytkownika',
    'password' => 'Hasło',
    'legend' => 'Legenda',
    'upload_rights' => 'Po wybraniu tej opcji użytkownik może przesyłać zawartość.',
    'edit_rights' => 'Po wybraniu tej opcji użytkownik może zmodyfikować swój profil (nazwa użytkownika, hasło).',
    'upload_trust_level' => 'Poziom zaufania przesyłania — określa, czy przesłane pliki są od razu publiczne.',

    'quota' => 'Po ustawieniu użytkownik ma limit miejsca na zdjęcia (w kB).',
    'user_deleted' => 'Użytkownik usunięty',
    'user_created' => 'Uzytkownik utworzony',
    'user_updated' => 'Aktualizacja użytkownika',
    'change_saved' => 'Zmiana zapisana!',
    'create_edit' => [
        'upload_rights' => 'Użytkownik może przesyłać treści.',
        'edit_rights' => 'Użytkownik może modyfikować swój profil (nazwa użytkownika, hasło).',
        'admin_rights' => 'Użytkownik ma uprawnienia administratora.',
        'upload_trust_level' => 'Poziom zaufania przesyłania',
        'upload_trust_level_check' => 'Sprawdzanie – przesłane pliki wymagają zatwierdzenia przez administratora, zanim staną się publiczne.',
        'upload_trust_level_monitor' => 'Monitorowanie – przesłane pliki są publiczne, chyba że zostaną oflagowane ze względu na treść.',
        'upload_trust_level_trust_but_verify' => 'Ufaj, ale sprawdzaj – przesłane pliki są publiczne; wyniki do przeglądu zatwierdzane automatycznie, obsługa wyników blokujących konfigurowalna.',
        'upload_trust_level_trusted' => 'Zaufany – przesłane pliki są od razu publiczne.',
        'trust_level_options' => [
            'trusted' => 'Zaufany',
            'trust_but_verify' => 'Ufaj, ale sprawdzaj',
            'monitor' => 'Monitorowanie',
            'check' => 'Sprawdzanie',
        ],
        'quota' => 'Użytkownik ma limit miejsca',
        'quota_kb' => 'miejsce na zdjęcia w kB (domyślnie 0)',
        'note' => 'Uwaga administratora (niewidoczna publicznie)',
        'create' => 'Utwórz',
        'edit' => 'Edytuj',
    ],
    'invite' => [
        'button' => 'Zaproś użytkownika',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Ten link jest ważny przez %d dni.',
    ],
    'line' => [
        'owner' => 'Właściciel',
        'admin' => 'Użytkownik admin',
        'edit' => 'Edytuj',
        'delete' => 'Usuń',
    ],
];
