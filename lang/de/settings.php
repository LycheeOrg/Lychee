<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => 'Einstellungen',
    'small_screen' => 'Für eine bessere Benutzererfahrung auf der Einstellungsseite,<br />empfehlen wir Ihnen, einen größeren Bildschirm zu verwenden.',
    'tabs' => [
        'basic' => 'Grundeinstellungen',
        'all_settings' => 'Alle Einstellungen',
    ],
    'toasts' => [
        'change_saved' => 'Änderung gespeichert!',
        'details' => 'Die Einstellungen wurden wie gewünscht geändert',
        'error' => 'Fehler!',
        'error_load_css' => 'dist/user.css konnte nicht geladen werden',
        'error_load_js' => 'dist/custom.js konnte nicht geladen werden',
        'error_save_css' => 'CSS konnte nicht gespeichert werden',
        'error_save_js' => 'JS konnte nicht gespeichert werden',
        'thank_you' => 'Vielen Dank für die Unterstützung.',
        'reload' => 'Laden Sie Ihre Seite neu für vollständige Funktionalität.',
    ],
    'system' => [
        'header' => 'System',
        'use_dark_mode' => 'Dunklen Modus für Lychee verwenden',
        'language' => 'Von Lychee verwendete Sprache',
        'nsfw_album_visibility' => 'Sensible Alben standardmäßig sichtbar machen.',
        'nsfw_album_explanation' => 'Wenn das Album öffentlich ist, ist es immer noch zugänglich, es ist nur nicht sichtbar und<b>kann durch Drücken von <kbd>H</kbd></b> angezeigt werden.',
        'cache_enabled' => 'Zwischenspeicherung von Antworten aktivieren.',
        'cache_enabled_details' => 'Das wird die Antwortzeit von Lychee erheblich beschleunigen.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Wenn Sie passwortgeschützte Alben verwenden, sollten Sie diese Funktion nicht aktivieren.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Erhalte exklusive Funktionen und unterstütze die Entwicklung von Lychee. Schalten die <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE Edition</a> frei.',
        'preview' => 'Vorschau der Lychee SE-Funktionen einschalten',
        'hide_call4action' => 'Lychee SE-Anmeldeformular ausblenden. Ich bin mit Lychee so zufrieden, wie es ist. :)',
        'hide_warning' => 'Wenn diese Option aktiviert ist, können Sie Ihren Lizenzschlüssel nur über die obige Registerkarte "Mehr" registrieren. Die Änderungen werden beim Neuladen der Seite übernommen.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Um Fotos aus deinem Dropbox-Konto zu importieren, benötigst du einen gültigen „Drop-ins App Key“ von der Dropbox-Website.',
        'api_key' => 'Dropbox API Schlüssel',
        'set_key' => 'Dropbox-Schlüssel festlegen',
    ],
    'gallery' => [
        'header' => 'Galerie',
        'photo_order_column' => 'Standardspalte verwendet für die Sortierung von Fotos',
        'photo_order_direction' => 'Standardreihenfolge für die Sortierung von Fotos',
        'album_order_column' => 'Standardspalte verwendet für die Sortierung von Alben',
        'album_order_direction' => 'Standardreihenfolge für die Sortierung von Alben',
        'aspect_ratio' => 'Standard-Seitenverhältnis für Album-Thumbnails',
        'photo_layout' => 'Layout für Bilder',
        'album_decoration' => 'Hinweise auf dem Albumcover anzeigen (Anzahl der Unteralben und/oder Fotos)',
        'album_decoration_direction' => 'Album Hinweise horizontal oder vertikal ausrichten',
        'photo_overlay' => 'Standard-Bildüberlagerungsinformationen',
        'license_default' => 'Standardlizenz für Alben',
        'license_help' => 'Brauchen Sie Hilfe bei der Auswahl?',
    ],
    'geolocation' => [
        'header' => 'Geografischer Standort',
        'map_display' => 'Anzeige der Karte mit GPS-Koordinaten',
        'map_display_public' => 'Anonymen Benutzern den Zugriff auf die Karte ermöglichen',
        'map_provider' => 'Kartenanbieter festlegen',
        'map_include_subalbums' => 'Bilder der Unteralben auf der Karte anzeigen',
        'location_decoding' => 'GPS-Standortdecodierung verwenden',
        'location_show' => 'Aus GPS-Koordinaten extrahierten Standort anzeigen',
        'location_show_public' => 'Anonyme Nutzer können auf den aus den GPS-Koordinaten extrahierten Standort zugreifen',
    ],
    'cssjs' => [
        'header' => 'Benutzerdefinierte CSS & JS',
        'change_css' => 'CSS ändern',
        'change_js' => 'JS ändern',
    ],
    'all' => [
        'old_setting_style' => 'Stil der "Alten Einstellungen"',
        'expert_settings' => 'Experten Modus',
        'change_detected' => 'Einige Einstellungen wurden geändert.',
        'save' => 'Speichern',
        'back_to_settings' => 'Zurück zu den gruppierten Einstellungen',
    ],
    'tool_option' => [
        'disabled' => 'deaktiviert',
        'enabled' => 'aktiviert',
        'discover' => 'entdecken',
    ],
    'groups' => [
        'general' => 'Allgemein',
        'system' => 'System',
        'modules' => 'Module',
        'advanced' => 'Fortgeschrittene',
    ],
];
