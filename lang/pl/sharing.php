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
    'bluk_share' => 'Bulk share',
    'bulk_share_instr' => 'Select multiple albums and users to share with.',
    'albums' => 'Albums',
    'users' => 'Users',
    'no_users' => 'No selectable users.',
    'no_albums' => 'No selectable albums.',
    'grants' => [
        'read' => 'Przyznaje dostęp do odczytu',
        'original' => 'Zapewnia dostęp do oryginalnego zdjęcia',
        'download' => 'Zapewnia pobieranie',
        'upload' => 'Zapewnia dodawanie zdjęć',
        'edit' => 'Zapewnia edycje',
        'delete' => 'Zapewnia usuwanie',
    ],
];
