<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Beállítások',
    'small_screen' => 'Az oldal kényelmesebb használatához<br />nagyobb képernyő használatát javasoljuk.',
    'tabs' => [
        'basic' => 'Alap',
        'all_settings' => 'Összes beállítás',
    ],
    'toasts' => [
        'change_saved' => 'Változás mentve!',
        'details' => 'A beállítások a kérésnek megfelelően módosultak',
        'error' => 'Hiba!',
        'error_load_css' => 'Nem sikerült betölteni: dist/user.css',
        'error_load_js' => 'Nem sikerült betölteni: dist/custom.js',
        'error_save_css' => 'Nem sikerült menteni a CSS-t',
        'error_save_js' => 'Nem sikerült menteni a JS-t',
        'thank_you' => 'Köszönjük a támogatását.',
        'reload' => 'A teljes funkcionalitáshoz töltse újra az oldalt.',
    ],
    'system' => [
        'header' => 'Rendszer',
        'use_dark_mode' => 'Sötét mód használata a Lychee-ben',
        'language' => 'A Lychee által használt nyelv',
        'nsfw_album_visibility' => 'Érzékeny albumok alapértelmezett láthatóvá tétele.',
        'nsfw_album_explanation' => 'Ha az album nyilvános, továbbra is elérhető, csak el van rejtve a nézetből, és <b>a <kbd>H</kbd> billentyű lenyomásával felfedhető</b>.',
        'cache_enabled' => 'Válaszok gyorsítótárazásának engedélyezése.',
        'cache_enabled_details' => 'Ez jelentősen felgyorsítja a Lychee válaszidejét.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Ha jelszóval védett albumokat használ, ne engedélyezze ezt.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Szerezzen exkluzív funkciókat, és támogassa a Lychee fejlesztését. Oldja fel az <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE kiadást</a>.',
        'preview' => 'A Lychee SE funkciók előnézetének engedélyezése',
        'hide_call4action' => 'A Lychee SE regisztrációs űrlap elrejtése. Elégedett vagyok a Lychee jelenlegi formájával. :)',
        'hide_warning' => 'Ha engedélyezve van, a licenckulcsot csak a fenti Továbbiak fülön lehet regisztrálni. A változások az oldal újratöltésekor lépnek életbe.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'A fényképek Dropboxból való importálásához érvényes drop-ins alkalmazáskulcs szükséges a Dropbox weboldaláról.',
        'api_key' => 'Dropbox API-kulcs',
        'set_key' => 'Dropbox-kulcs beállítása',
    ],
    'gallery' => [
        'header' => 'Galéria',
        'photo_order_column' => 'Fényképek rendezésének alapértelmezett oszlopa',
        'photo_order_direction' => 'Fényképek rendezésének alapértelmezett iránya',
        'album_order_column' => 'Albumok rendezésének alapértelmezett oszlopa',
        'album_order_direction' => 'Albumok rendezésének alapértelmezett iránya',
        'aspect_ratio' => 'Albumbélyegképek alapértelmezett képaránya',
        'photo_layout' => 'Képek elrendezése',
        'album_decoration' => 'Díszítések megjelenítése az album borítóján (alalbumok és/vagy fényképek száma)',
        'album_decoration_direction' => 'Albumdíszítések vízszintes vagy függőleges igazítása',
        'photo_overlay' => 'Alapértelmezett képátfedési információk',
        'album_date_scrubber_enabled' => 'Dátumcsúszka megjelenítése az albumnézetekben',
        'rounded_corners_enabled' => 'Fénykép- és albumbélyegképek sarkainak lekerekítése',
        'album_border_enabled' => 'Keret megjelenítése a fénykép- és albumbélyegképek körül',
        'photo_ken_burns_on_hover_enabled' => 'Ken Burns-effektus engedélyezése a fénykép fölé vitt egérnél',
        'photo_ken_burns_on_hover_scale' => 'Ken Burns-nagyítás mértéke rámutatáskor (%)',
        'photo_ken_burns_on_hover_duration' => 'Ken Burns-nagyítás időtartama rámutatáskor (másodperc)',
        'license_default' => 'Albumok alapértelmezett licence',
        'license_help' => 'Segítségre van szüksége a választáshoz?',
    ],
    'geolocation' => [
        'header' => 'Földrajzi hely',
        'map_display' => 'Térkép megjelenítése GPS-koordináták alapján',
        'map_display_public' => 'A térkép elérésének engedélyezése névtelen felhasználók számára',
        'map_provider' => 'A térképszolgáltató meghatározása',
        'map_include_subalbums' => 'Az alalbumok képeinek megjelenítése a térképen',
        'location_decoding' => 'GPS-helymeghatározás dekódolásának használata',
        'location_show' => 'A GPS-koordinátákból kinyert hely megjelenítése',
        'location_show_public' => 'A névtelen felhasználók hozzáférhetnek a GPS-koordinátákból kinyert helyhez',
        'gps_coordinate_display' => 'GPS-koordináták megjelenítése',
        'gps_coordinate_display_public' => 'A GPS-koordináták elérésének engedélyezése névtelen felhasználók számára',
    ],
    'cssjs' => [
        'header' => 'Egyéni CSS és JS',
        'change_css' => 'CSS módosítása',
        'change_js' => 'JS módosítása',
    ],
    'all' => [
        'old_setting_style' => 'Régi beállítási stílus',
        'expert_settings' => 'Szakértői mód',
        'change_detected' => 'Néhány beállítás megváltozott.',
        'save' => 'Mentés',
        'back_to_settings' => 'Vissza a csoportosított beállításokhoz',
    ],
    'tool_option' => [
        'disabled' => 'letiltva',
        'enabled' => 'engedélyezve',
        'discover' => 'felfedezés',
    ],
    'groups' => [
        'general' => 'Általános',
        'system' => 'Rendszer',
        'modules' => 'Modulok',
        'advanced' => 'Speciális',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Adminisztrációs irányítópult használata',
            'help' => 'A beágyazott adminisztrációs almenü lecserélése egyetlen hivatkozásra, amely az új adminisztrációs irányítópultra mutat.',
        ],
    ],
    'password_field' => [
        'set' => 'Beállítva',
        'not_set' => 'Nincs beállítva',
        'placeholder' => 'Új jelszó',
        'clear' => 'Törlés',
    ],
];
