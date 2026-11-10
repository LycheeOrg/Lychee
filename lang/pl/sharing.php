<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Udostępnianie',
    'info' => 'Ta strona zawiera przegląd i możliwość edycji praw udostępniania powiązanych z albumami.',
    'album_title' => 'Tytuł albumu',
    'username' => 'Nazwa użytkownika',
    'no_data' => 'Lista udostępniania jest pusta.',
    'filter_placeholder' => 'Szukaj po tytule albumu...',
    'hide_empty' => 'Ukryj albumy bez udostępnień',
    'screen_too_small' => 'Ta strona wymaga większego ekranu.',
    'share' => 'Udostępnij',
    'add_new_access_permission' => 'Dodaj nowe uprawnienie dostępu',
    'permission_deleted' => 'Pozwolenie usunięte!',
    'permission_created' => 'Pozwolenie utworzone!',
    'propagate' => 'Rozpowszechnianie',
    'propagate_help' => 'Propagowanie bieżących uprawnień dostępu do wszystkich elementów potomnych<br>(podalbumów i ich odpowiednich podalbumów itd.)',
    'propagate_default' => 'Domyślnie istniejące uprawnienia (album-użytkownik)<br>są aktualizowane, a brakujące dodawane.<br>Dodatkowe uprawnienia, których nie ma na tej liście, pozostają nietknięte.',
    'propagate_overwrite' => 'Nadpisz istniejące uprawnienia zamiast aktualizować.<br>Usunie to również wszystkie uprawnienia nieobecne na tej liście.',
    'propagate_warning' => 'Tego działania nie można cofnąć.',
    'permission_overwritten' => 'Propagacja powiodła się! Uprawnienie nadpisane!',
    'permission_updated' => 'Propagacja zakończona sukcesem! Pozwolenie zaktualizowane!',
    'bluk_share' => 'Udostępnianie zbiorcze',
    'bulk_share_instr' => 'Wybierz wiele albumów i użytkowników, którym chcesz je udostępnić.',
    'albums' => 'Albumy',
    'users' => 'Użytkownicy',
    'no_users' => 'Brak użytkowników do wyboru.',
    'no_albums' => 'Brak albumów do wyboru.',
    'grants' => [
        'read' => 'Przyznaje dostęp do odczytu',
        'original' => 'Zapewnia dostęp do oryginalnego zdjęcia',
        'download' => 'Zapewnia pobieranie',
        'upload' => 'Zapewnia dodawanie zdjęć',
        'edit' => 'Zapewnia edycje',
        'move' => 'Pozwala przenosić, kopiować i scalać',
        'delete' => 'Zapewnia usuwanie',
    ],
];
