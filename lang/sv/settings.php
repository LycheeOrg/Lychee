<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Inställningar',
    'small_screen' => 'För en bättre upplevelse på den här sidan<br />rekommenderar vi att du använder en större skärm.',
    'tabs' => [
        'basic' => 'Grundläggande',
        'all_settings' => 'Alla inställningar',
    ],
    'toasts' => [
        'change_saved' => 'Ändringen har sparats!',
        'details' => 'Inställningarna har ändrats enligt begäran',
        'error' => 'Fel!',
        'error_load_css' => 'Det gick inte att läsa in dist/user.css',
        'error_load_js' => 'Det gick inte att läsa in dist/custom.js',
        'error_save_css' => 'Det gick inte att spara CSS',
        'error_save_js' => 'Det gick inte att spara JS',
        'thank_you' => 'Tack för ditt stöd.',
        'reload' => 'Ladda om sidan för full funktionalitet.',
    ],
    'system' => [
        'header' => 'System',
        'use_dark_mode' => 'Använd mörkt läge för Lychee',
        'language' => 'Språk som används av Lychee',
        'nsfw_album_visibility' => 'Gör känsliga album synliga som standard.',
        'nsfw_album_explanation' => 'Om albumet är offentligt är det fortfarande tillgängligt, bara dolt från vyn, och <b>kan visas genom att trycka på <kbd>H</kbd></b>.',
        'cache_enabled' => 'Aktivera cachning av svar.',
        'cache_enabled_details' => 'Detta snabbar upp svarstiden för Lychee avsevärt.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Om du använder lösenordsskyddade album bör du inte aktivera detta.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Få exklusiva funktioner och stöd utvecklingen av Lychee. Lås upp <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE-utgåvan</a>.',
        'preview' => 'Aktivera förhandsvisning av Lychee SE-funktioner',
        'hide_call4action' => 'Dölj registreringsformuläret för Lychee SE. Jag är nöjd med Lychee som det är. :)',
        'hide_warning' => 'Om detta aktiveras kan du bara registrera din licensnyckel via fliken Mer ovan. Ändringarna tillämpas när sidan laddas om.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'För att importera foton från din Dropbox behöver du en giltig app-nyckel för drop-ins från deras webbplats.',
        'api_key' => 'Dropbox API-nyckel',
        'set_key' => 'Ange Dropbox-nyckel',
    ],
    'gallery' => [
        'header' => 'Galleri',
        'photo_order_column' => 'Standardkolumn för sortering av foton',
        'photo_order_direction' => 'Standardordning för sortering av foton',
        'album_order_column' => 'Standardkolumn för sortering av album',
        'album_order_direction' => 'Standardordning för sortering av album',
        'aspect_ratio' => 'Standardbildförhållande för albumminiatyrer',
        'photo_layout' => 'Layout för bilder',
        'album_decoration' => 'Visa dekorationer på albumomslag (antal underalbum och/eller foton)',
        'album_decoration_direction' => 'Justera albumdekorationer horisontellt eller vertikalt',
        'photo_overlay' => 'Standardinformation i bildöverlägg',
        'album_date_scrubber_enabled' => 'Visa datumreglaget i albumvyer',
        'rounded_corners_enabled' => 'Runda hörnen på foto- och albumminiatyrer',
        'album_border_enabled' => 'Visa en ram runt foto- och albumminiatyrer',
        'photo_ken_burns_on_hover_enabled' => 'Aktivera en Ken Burns-effekt när muspekaren är över ett foto',
        'photo_ken_burns_on_hover_scale' => 'Zoomgrad för Ken Burns vid hovring (%)',
        'photo_ken_burns_on_hover_duration' => 'Zoomtid för Ken Burns vid hovring (sekunder)',
        'license_default' => 'Standardlicens för album',
        'license_help' => 'Behöver du hjälp att välja?',
    ],
    'geolocation' => [
        'header' => 'Geoplats',
        'map_display' => 'Visa kartan utifrån GPS-koordinater',
        'map_display_public' => 'Tillåt anonyma användare att se kartan',
        'map_provider' => 'Anger kartleverantör',
        'map_include_subalbums' => 'Inkludera bilder från underalbum på kartan',
        'location_decoding' => 'Använd avkodning av GPS-plats',
        'location_show' => 'Visa plats som tagits fram från GPS-koordinater',
        'location_show_public' => 'Anonyma användare kan se platsen som tagits fram från GPS-koordinater',
        'gps_coordinate_display' => 'Visa GPS-koordinaterna',
        'gps_coordinate_display_public' => 'Tillåt anonyma användare att se GPS-koordinaterna',
    ],
    'cssjs' => [
        'header' => 'Anpassad CSS och JS',
        'change_css' => 'Ändra CSS',
        'change_js' => 'Ändra JS',
    ],
    'all' => [
        'old_setting_style' => 'Gammal inställningsstil',
        'expert_settings' => 'Expertläge',
        'change_detected' => 'Vissa inställningar har ändrats.',
        'save' => 'Spara',
        'back_to_settings' => 'Tillbaka till grupperade inställningar',
    ],
    'tool_option' => [
        'disabled' => 'inaktiverad',
        'enabled' => 'aktiverad',
        'discover' => 'upptäck',
    ],
    'groups' => [
        'general' => 'Allmänt',
        'system' => 'System',
        'modules' => 'Moduler',
        'advanced' => 'Avancerat',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Använd adminpanelen',
            'help' => 'Ersätt den nästlade adminundermenyn med en enda länk till den nya adminpanelsidan.',
        ],
    ],
    'password_field' => [
        'set' => 'Angivet',
        'not_set' => 'Inte angivet',
        'placeholder' => 'Nytt lösenord',
        'clear' => 'Rensa',
    ],
];
