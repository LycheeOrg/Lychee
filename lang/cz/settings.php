<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Nastavení',
    'small_screen' => 'Pro lepší zážitek na stránce Nastavení<br/>doporučujeme použít větší obrazovku.',
    'tabs' => [
        'basic' => 'Základní',
        'all_settings' => 'Všechna nastavení',
    ],
    'toasts' => [
        'change_saved' => 'Změna uložena!',
        'details' => 'Nastavení byla upravena podle požadavku',
        'error' => 'Chyba!',
        'error_load_css' => 'Nelze načíst dist/user.css',
        'error_load_js' => 'Nelze načíst dist/custom.js',
        'error_save_css' => 'Nelze uložit CSS',
        'error_save_js' => 'Nelze uložit JS',
        'thank_you' => 'Děkujeme za vaši podporu.',
        'reload' => 'Obnovte stránku pro plnou funkčnost.',
    ],
    'system' => [
        'header' => 'Systém',
        'use_dark_mode' => 'Použít tmavý režim pro Lychee',
        'language' => 'Jazyk používaný v Lychee',
        'nsfw_album_visibility' => 'Zobrazit citlivá alba ve výchozím nastavení.' ,
        'nsfw_album_explanation' => 'Pokud je album veřejné, je stále přístupné, pouze skryté z pohledu a <b>lze jej odhalit stisknutím <kbd>H</kbd></b>.',
        'cache_enabled' => 'Povolit ukládání odpovědí do mezipaměti.',
        'cache_enabled_details' => 'Toto výrazně zrychlí dobu odezvy Lychee. <br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Pokud používáte alba chráněná heslem, neměli byste tuto možnost povolit.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Získejte exkluzivní funkce a podpořte vývoj Lychee. Odemkněte <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edici SE</a>.',
        'preview' => 'Povolit náhled funkcí Lychee SE',
        'hide_call4action' => 'Skrýt tento registrační formulář pro Lychee SE. Jsem spokojený s Lychee tak, jak je. :)',
        'hide_warning' => 'Pokud je tato možnost povolena, jediným způsobem, jak zaregistrovat svůj licenční klíč, bude záložka Více nahoře. Změny se projeví po obnovení stránky.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Chcete-li importovat fotografie z Dropboxu, potřebujete platný klíč aplikace drop-ins z jejich webových stránek.',
        'api_key' => 'Klíč API Dropboxu',
        'set_key' => 'Nastavit klíč Dropboxu',
    ],
    'gallery' => [
        'header' => 'Galerie',
        'photo_order_column' => 'Výchozí sloupec pro řazení fotografií',
        'photo_order_direction' => 'Výchozí pořadí pro řazení fotografií',
        'album_order_column' => 'Výchozí sloupec pro řazení alb',
        'album_order_direction' => 'Výchozí pořadí pro řazení alb',
        'aspect_ratio' => 'Výchozí poměr stran pro miniatury alb',
        'photo_layout' => 'Rozložení obrázků',
        'album_decoration' => 'Zobrazit dekorace na obálce alba (počet podalb a/nebo fotografií)',
        'album_decoration_direction' => 'Zarovnat dekorace alba vodorovně nebo svisle',
        'photo_overlay' => 'Výchozí informace o překrytí obrázků',
        'license_default' => 'Výchozí licence používaná pro alba',
        'license_help' => 'Potřebujete pomoc s výběrem?',
    ],
    'geolocation' => [
        'header' => 'Geolokace',
        'map_display' => 'Zobrazit mapu na základě GPS souřadnic',
        'map_display_public' => 'Povolit anonymním uživatelům přístup k mapě',
        'map_provider' => 'Definuje poskytovatele mapy',
        'map_include_subalbums' => 'Zahrnout obrázky z podalb do mapy',
        'location_decoding' => 'Použít dekódování polohy GPS',
        'location_show' => 'Zobrazit polohu získanou z GPS souřadnic',
        'location_show_public' => 'Anonymní uživatelé mají přístup k poloze extrahované z GPS souřadnic',
        'gps_coordinate_display' => 'Zobrazit GPS souřadnice',
        'gps_coordinate_display_public' => 'Povolit anonymním uživatelům přístup k GPS souřadnicím',
    ],
    'cssjs' => [
        'header' => 'Vlastní CSS a JS',
        'change_css' => 'Změnit CSS',
        'change_js' => 'Změnit JS',
    ],
    'all' => [
        'old_setting_style' => 'Starý styl nastavení',
        'expert_settings' => 'Režim pro pokročilé',
        'change_detected' => 'Některá nastavení se změnila.',
        'save' => 'Uložit',
        'back_to_settings' => 'Zpět ke seskupeným nastavením',
    ],
    'tool_option' => [
        'disabled' => 'zakázáno',
        'enabled' => 'povoleno',
        'discover' => 'objevit',
    ],
    'groups' => [
        'general' => 'Obecné',
        'system' => 'Systém',
        'modules' => 'Moduly',
        'advanced' => 'Pokročilé',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Použít administrační panel',
            'help' => 'Nahraďte vnořené podmenu administrace jediným odkazem na novou stránku administračního panelu.',
        ],
    ],
];
