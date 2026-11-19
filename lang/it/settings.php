<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Impostazioni',
    'small_screen' => 'Per una migliore esperienza su questa pagina,<br />ti consigliamo di usare uno schermo più grande.',
    'tabs' => [
        'basic' => 'Base',
        'all_settings' => 'Tutte le impostazioni',
    ],
    'toasts' => [
        'change_saved' => 'Modifica salvata!',
        'details' => 'Le impostazioni sono state modificate come richiesto',
        'error' => 'Errore!',
        'error_load_css' => 'Impossibile caricare dist/user.css',
        'error_load_js' => 'Impossibile caricare dist/custom.js',
        'error_save_css' => 'Impossibile salvare il CSS',
        'error_save_js' => 'Impossibile salvare il JS',
        'thank_you' => 'Grazie per il tuo supporto.',
        'reload' => 'Ricarica la pagina per avere tutte le funzionalità.',
    ],
    'system' => [
        'header' => 'Sistema',
        'use_dark_mode' => 'Usa la modalità scura per Lychee',
        'language' => 'Lingua usata da Lychee',
        'nsfw_album_visibility' => 'Rendi visibili per impostazione predefinita gli album sensibili.',
        'nsfw_album_explanation' => 'Se l\'album è pubblico, rimane comunque accessibile, ma è nascosto dalla vista e <b>può essere mostrato premendo <kbd>H</kbd></b>.',
        'cache_enabled' => 'Abilita la cache delle risposte.',
        'cache_enabled_details' => 'Questo velocizzerà notevolmente i tempi di risposta di Lychee.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Se usi album protetti da password, non dovresti abilitare questa opzione.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Ottieni funzionalità esclusive e sostieni lo sviluppo di Lychee. Sblocca l\'<a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">edizione SE</a>.',
        'preview' => 'Abilita l\'anteprima delle funzionalità di Lychee SE',
        'hide_call4action' => 'Nascondi questo modulo di registrazione di Lychee SE. Sono soddisfatto di Lychee così com\'è. :)',
        'hide_warning' => 'Se abilitato, l\'unico modo per registrare la tua chiave di licenza sarà tramite la scheda Altro qui sopra. Le modifiche vengono applicate al ricaricamento della pagina.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Per importare foto dal tuo Dropbox, ti serve una chiave app drop-ins valida, ottenibile dal loro sito web.',
        'api_key' => 'Chiave API di Dropbox',
        'set_key' => 'Imposta chiave Dropbox',
    ],
    'gallery' => [
        'header' => 'Galleria',
        'photo_order_column' => 'Colonna predefinita per l\'ordinamento delle foto',
        'photo_order_direction' => 'Ordine predefinito per l\'ordinamento delle foto',
        'album_order_column' => 'Colonna predefinita per l\'ordinamento degli album',
        'album_order_direction' => 'Ordine predefinito per l\'ordinamento degli album',
        'aspect_ratio' => 'Proporzioni predefinite per le miniature degli album',
        'photo_layout' => 'Layout delle immagini',
        'album_decoration' => 'Mostra decorazioni sulla copertina dell\'album (numero di sotto-album e/o foto)',
        'album_decoration_direction' => 'Allinea le decorazioni degli album orizzontalmente o verticalmente',
        'photo_overlay' => 'Informazioni predefinite in sovrimpressione sull\'immagine',
        'album_date_scrubber_enabled' => 'Mostra la barra di navigazione per data nelle viste degli album',
        'rounded_corners_enabled' => 'Arrotonda gli angoli delle miniature di foto e album',
        'album_border_enabled' => 'Mostra un bordo attorno alle miniature di foto e album',
        'photo_ken_burns_on_hover_enabled' => 'Abilita l\'effetto Ken Burns al passaggio del mouse sulle foto',
        'photo_ken_burns_on_hover_scale' => 'Livello di zoom Ken Burns al passaggio del mouse (%)',
        'photo_ken_burns_on_hover_duration' => 'Durata dello zoom Ken Burns al passaggio del mouse (secondi)',
        'license_default' => 'Licenza predefinita per gli album',
        'license_help' => 'Ti serve aiuto per scegliere?',
    ],
    'geolocation' => [
        'header' => 'Geolocalizzazione',
        'map_display' => 'Mostra la mappa in base alle coordinate GPS',
        'map_display_public' => 'Consenti agli utenti anonimi di accedere alla mappa',
        'map_provider' => 'Definisce il fornitore della mappa',
        'map_include_subalbums' => 'Include le immagini dei sotto-album nella mappa',
        'location_decoding' => 'Usa la decodifica della posizione GPS',
        'location_show' => 'Mostra la posizione ricavata dalle coordinate GPS',
        'location_show_public' => 'Gli utenti anonimi possono accedere alla posizione ricavata dalle coordinate GPS',
        'gps_coordinate_display' => 'Mostra le coordinate GPS',
        'gps_coordinate_display_public' => 'Consenti agli utenti anonimi di accedere alle coordinate GPS',
    ],
    'cssjs' => [
        'header' => 'CSS e JS personalizzati',
        'change_css' => 'Modifica CSS',
        'change_js' => 'Modifica JS',
    ],
    'all' => [
        'old_setting_style' => 'Stile impostazioni precedente',
        'expert_settings' => 'Modalità esperto',
        'change_detected' => 'Alcune impostazioni sono state modificate.',
        'save' => 'Salva',
        'back_to_settings' => 'Torna alle impostazioni raggruppate',
    ],
    'tool_option' => [
        'disabled' => 'disabilitato',
        'enabled' => 'abilitato',
        'discover' => 'rileva',
    ],
    'groups' => [
        'general' => 'Generale',
        'system' => 'Sistema',
        'modules' => 'Moduli',
        'advanced' => 'Avanzate',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => 'Usa la dashboard di amministrazione',
            'help' => 'Sostituisce il sottomenu di amministrazione annidato con un unico link alla nuova pagina della dashboard di amministrazione.',
        ],
    ],
    'password_field' => [
        'set' => 'Impostata',
        'not_set' => 'Non impostata',
        'placeholder' => 'Nuova password',
        'clear' => 'Cancella',
    ],
];
