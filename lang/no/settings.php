<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Innstillinger',
    'small_screen' => 'For en bedre opplevelse på Innstillinger-siden,<br />anbefaler vi at du bruker en større skjerm.',
    'tabs' => [
        'basic' => 'Grunnleggende',
        'all_settings' => 'Alle innstillinger',
    ],
    'toasts' => [
        'change_saved' => 'Endringen er lagret!',
        'details' => 'Innstillingene er endret i henhold til forespørsel',
        'error' => 'Feil!',
        'error_load_css' => 'Kunne ikke laste dist/user.css',
        'error_load_js' => 'Kunne ikke laste dist/custom.js',
        'error_save_css' => 'Kunne ikke lagre CSS',
        'error_save_js' => 'Kunne ikke lagre JS',
        'thank_you' => 'Takk for din støtte.',
        'reload' => 'Last inn siden på nytt for full funksjonalitet.',
    ],
    'system' => [
        'header' => 'System',
        'use_dark_mode' => 'Bruk mørk modus for Lychee',
        'language' => 'Språk brukt av Lychee',
        'nsfw_album_visibility' => 'Gjør følsomme album synlige som standard.',
        'nsfw_album_explanation' => 'Hvis albumet er offentlig, er det fortsatt tilgjengelig, bare skjult fra visning og <b>kan avsløres ved å trykke <kbd>H</kbd></b>.',
        'cache_enabled' => 'Aktiver mellomlagring av svar.',
        'cache_enabled_details' => 'Dette vil betydelig øke svartiden til Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Hvis du bruker passordbeskyttede album, bør du ikke aktivere dette.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Få eksklusive funksjoner og støtt utviklingen av Lychee. Lås opp <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE-utgaven</a>.',
        'preview' => 'Aktiver forhåndsvisning av Lychee SE-funksjoner',
        'hide_call4action' => 'Skjul dette registreringsskjemaet for Lychee SE. Jeg er fornøyd med Lychee som den er. :)',
        'hide_warning' => 'Hvis aktivert, vil den eneste måten å registrere lisensnøkkelen din på være via Mer-fanen ovenfor. Endringer trer i kraft når siden lastes inn på nytt.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'For å importere bilder fra Dropbox-en din trenger du en gyldig drop-ins-appnøkkel fra nettsiden deres.',
        'api_key' => 'Dropbox API-nøkkel',
        'set_key' => 'Angi Dropbox-nøkkel',
    ],
    'gallery' => [
        'header' => 'Galleri',
        'photo_order_column' => 'Standardkolonne brukt for sortering av bilder',
        'photo_order_direction' => 'Standardrekkefølge brukt for sortering av bilder',
        'album_order_column' => 'Standardkolonne brukt for sortering av album',
        'album_order_direction' => 'Standardrekkefølge brukt for sortering av album',
        'aspect_ratio' => 'Standard sideforhold for albumminiatyrer',
        'photo_layout' => 'Layout for bilder',
        'album_decoration' => 'Vis dekorasjoner på albumforside (antall underalbum og/eller bilder)',
        'album_decoration_direction' => 'Juster albumdekorasjoner horisontalt eller vertikalt',
        'photo_overlay' => 'Standard informasjon for bildeoverlegg',
        'rounded_corners_enabled' => 'Avrund hjørnene på bilde- og albumminiatyrer',
        'album_border_enabled' => 'Vis en kant rundt bilde- og albumminiatyrer',
        'license_default' => 'Standardlisens brukt for album',
        'license_help' => 'Trenger du hjelp til å velge?',
    ],
    'geolocation' => [
        'header' => 'Geolokasjon',
        'map_display' => 'Vis kartet basert på GPS-koordinater',
        'map_display_public' => 'Tillat anonyme brukere å få tilgang til kartet',
        'map_provider' => 'Definerer kartleverandøren',
        'map_include_subalbums' => 'Inkluder bilder fra underalbumene på kartet',
        'location_decoding' => 'Bruk avkoding av GPS-posisjon',
        'location_show' => 'Vis sted hentet fra GPS-koordinater',
        'location_show_public' => 'Anonyme brukere kan få tilgang til stedet hentet fra GPS-koordinater',
        'gps_coordinate_display' => 'Vis GPS-koordinatene',
        'gps_coordinate_display_public' => 'Tillat anonyme brukere å få tilgang til GPS-koordinatene',
    ],
    'cssjs' => [
        'header' => 'Egendefinert CSS og JS',
        'change_css' => 'Endre CSS',
        'change_js' => 'Endre JS',
    ],
    'all' => [
        'old_setting_style' => 'Gammel innstillingsstil',
        'expert_settings' => 'Ekspertmodus',
        'change_detected' => 'Noen innstillinger er endret.',
        'save' => 'Lagre',
        'back_to_settings' => 'Tilbake til grupperte innstillinger',
    ],
    'tool_option' => [
        'disabled' => 'deaktivert',
        'enabled' => 'aktivert',
        'discover' => 'oppdag',
    ],
    'groups' => [
        'general' => 'Generelt',
        'system' => 'System',
        'modules' => 'Moduler',
        'advanced' => 'Avansert',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Bruk administrasjonspanel',
            'help' => 'Erstatt den nøstede admin-undermenyen med én enkelt lenke til den nye administrasjonspanelsiden.',
        ],
    ],
];
