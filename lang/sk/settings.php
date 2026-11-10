<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Nastavenia',
    'small_screen' => 'Pre lepší zážitok na tejto stránke<br />odporúčame použiť väčšiu obrazovku.',
    'tabs' => [
        'basic' => 'Základné',
        'all_settings' => 'Všetky nastavenia',
    ],
    'toasts' => [
        'change_saved' => 'Zmena uložená!',
        'details' => 'Nastavenia boli upravené podľa požiadavky',
        'error' => 'Chyba!',
        'error_load_css' => 'Nepodarilo sa načítať dist/user.css',
        'error_load_js' => 'Nepodarilo sa načítať dist/custom.js',
        'error_save_css' => 'Nepodarilo sa uložiť CSS',
        'error_save_js' => 'Nepodarilo sa uložiť JS',
        'thank_you' => 'Ďakujeme za vašu podporu.',
        'reload' => 'Pre plnú funkčnosť obnovte stránku.',
    ],
    'system' => [
        'header' => 'Systém',
        'use_dark_mode' => 'Použiť tmavý režim pre Lychee',
        'language' => 'Jazyk používaný v Lychee',
        'nsfw_album_visibility' => 'Predvolene zobrazovať citlivé albumy.',
        'nsfw_album_explanation' => 'Ak je album verejný, je stále prístupný, iba skrytý zo zobrazenia, a <b>je možné ho odkryť stlačením <kbd>H</kbd></b>.',
        'cache_enabled' => 'Povoliť ukladanie odpovedí do cache.',
        'cache_enabled_details' => 'Výrazne to zrýchli čas odozvy Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Ak používate albumy chránené heslom, túto možnosť by ste nemali povoliť.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Získajte exkluzívne funkcie a podporte vývoj Lychee. Odomknite <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edíciu SE</a>.',
        'preview' => 'Povoliť ukážku funkcií Lychee SE',
        'hide_call4action' => 'Skryť tento registračný formulár Lychee SE. Som spokojný/á s Lychee tak, ako je. :)',
        'hide_warning' => 'Ak je povolené, licenčný kľúč bude možné zaregistrovať iba cez kartu Viac vyššie. Zmeny sa prejavia po obnovení stránky.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Na import fotografií z Dropboxu potrebujete platný kľúč aplikácie drop-ins z ich webovej stránky.',
        'api_key' => 'API kľúč Dropbox',
        'set_key' => 'Nastaviť kľúč Dropbox',
    ],
    'gallery' => [
        'header' => 'Galéria',
        'photo_order_column' => 'Predvolený stĺpec na zoraďovanie fotografií',
        'photo_order_direction' => 'Predvolené poradie zoraďovania fotografií',
        'album_order_column' => 'Predvolený stĺpec na zoraďovanie albumov',
        'album_order_direction' => 'Predvolené poradie zoraďovania albumov',
        'aspect_ratio' => 'Predvolený pomer strán miniatúr albumov',
        'photo_layout' => 'Rozloženie obrázkov',
        'album_decoration' => 'Zobraziť dekorácie na obale albumu (počet podalbumov a/alebo fotografií)',
        'album_decoration_direction' => 'Zarovnať dekorácie albumu vodorovne alebo zvislo',
        'photo_overlay' => 'Predvolené informácie v prekryve obrázka',
        'album_date_scrubber_enabled' => 'Zobraziť dátumový posuvník v zobrazeniach albumu',
        'rounded_corners_enabled' => 'Zaobliť rohy miniatúr fotografií a albumov',
        'album_border_enabled' => 'Zobraziť okraj okolo miniatúr fotografií a albumov',
        'photo_ken_burns_on_hover_enabled' => 'Povoliť efekt Ken Burns pri prejdení myšou nad fotografiou',
        'photo_ken_burns_on_hover_scale' => 'Miera priblíženia Ken Burns pri prejdení myšou (%)',
        'photo_ken_burns_on_hover_duration' => 'Trvanie priblíženia Ken Burns pri prejdení myšou (sekundy)',
        'license_default' => 'Predvolená licencia pre albumy',
        'license_help' => 'Potrebujete pomoc s výberom?',
    ],
    'geolocation' => [
        'header' => 'Geolokácia',
        'map_display' => 'Zobraziť mapu podľa GPS súradníc',
        'map_display_public' => 'Povoliť anonymným používateľom prístup k mape',
        'map_provider' => 'Určuje poskytovateľa mapy',
        'map_include_subalbums' => 'Zahrnúť obrázky z podalbumov na mapu',
        'location_decoding' => 'Použiť dekódovanie polohy z GPS',
        'location_show' => 'Zobraziť polohu získanú z GPS súradníc',
        'location_show_public' => 'Anonymní používatelia majú prístup k polohe získanej z GPS súradníc',
        'gps_coordinate_display' => 'Zobraziť GPS súradnice',
        'gps_coordinate_display_public' => 'Povoliť anonymným používateľom prístup ku GPS súradniciam',
    ],
    'cssjs' => [
        'header' => 'Vlastné CSS a JS',
        'change_css' => 'Zmeniť CSS',
        'change_js' => 'Zmeniť JS',
    ],
    'all' => [
        'old_setting_style' => 'Starý štýl nastavení',
        'expert_settings' => 'Expertný režim',
        'change_detected' => 'Niektoré nastavenia sa zmenili.',
        'save' => 'Uložiť',
        'back_to_settings' => 'Späť na zoskupené nastavenia',
    ],
    'tool_option' => [
        'disabled' => 'vypnuté',
        'enabled' => 'zapnuté',
        'discover' => 'zisťovať',
    ],
    'groups' => [
        'general' => 'Všeobecné',
        'system' => 'Systém',
        'modules' => 'Moduly',
        'advanced' => 'Pokročilé',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Použiť administrátorský panel',
            'help' => 'Nahradiť vnorené administrátorské podmenu jedným odkazom na novú stránku administrátorského panela.',
        ],
    ],
    'password_field' => [
        'set' => 'Nastavené',
        'not_set' => 'Nenastavené',
        'placeholder' => 'Nové heslo',
        'clear' => 'Vymazať',
    ],
];
