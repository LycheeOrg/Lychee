<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Megosztás',
    'info' => 'Ez az oldal áttekintést ad az albumokhoz tartozó megosztási jogokról, és lehetővé teszi azok szerkesztését.',
    'album_title' => 'Album címe',
    'username' => 'Felhasználónév',
    'no_data' => 'A megosztási lista üres.',
    'filter_placeholder' => 'Keresés albumcím szerint...',
    'hide_empty' => 'Megosztás nélküli albumok elrejtése',
    'screen_too_small' => 'Ehhez az oldalhoz nagyobb képernyő szükséges.',
    'share' => 'Megosztás',
    'add_new_access_permission' => 'Új hozzáférési jogosultság hozzáadása',
    'permission_deleted' => 'Jogosultság törölve!',
    'permission_created' => 'Jogosultság létrehozva!',
    'propagate' => 'Továbbörökítés',
    'propagate_help' => 'A jelenlegi hozzáférési jogosultságok továbbörökítése az összes leszármazottra<br>(alalbumok és azok alalbumai stb.)',
    'propagate_default' => 'Alapértelmezés szerint a meglévő jogosultságok (album-felhasználó)<br>frissülnek, a hiányzók pedig hozzáadódnak.<br>Az ebben a listában nem szereplő további jogosultságok változatlanok maradnak.',
    'propagate_overwrite' => 'A meglévő jogosultságok felülírása frissítés helyett.<br>Ez eltávolítja az ebben a listában nem szereplő összes jogosultságot is.',
    'propagate_warning' => 'Ez a művelet nem vonható vissza.',
    'permission_overwritten' => 'Sikeres továbbörökítés! Jogosultság felülírva!',
    'permission_updated' => 'Sikeres továbbörökítés! Jogosultság frissítve!',
    'bluk_share' => 'Tömeges megosztás',
    'bulk_share_instr' => 'Válasszon ki több albumot és felhasználót a megosztáshoz.',
    'albums' => 'Albumok',
    'users' => 'Felhasználók',
    'no_users' => 'Nincsenek kiválasztható felhasználók.',
    'no_albums' => 'Nincsenek kiválasztható albumok.',
    'grants' => [
        'read' => 'Olvasási hozzáférést ad',
        'original' => 'Hozzáférést ad az eredeti fényképhez',
        'download' => 'Letöltést engedélyez',
        'upload' => 'Feltöltést engedélyez',
        'edit' => 'Szerkesztést engedélyez',
        'move' => 'Áthelyezést, másolást és egyesítést engedélyez',
        'delete' => 'Törlést engedélyez',
    ],
];
