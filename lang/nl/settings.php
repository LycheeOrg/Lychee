<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Instellingen',
    'small_screen' => 'Voor een betere ervaring op de instellingenpagina,<br />raden we aan een groter scherm te gebruiken.',
    'tabs' => [
        'basic' => 'Basis',
        'all_settings' => 'Alle instellingen',
    ],
    'toasts' => [
        'change_saved' => 'Wijziging opgeslagen!',
        'details' => 'Instellingen zijn aangepast zoals gevraagd',
        'error' => 'Fout!',
        'error_load_css' => 'Kon dist/user.css niet laden',
        'error_load_js' => 'Kon dist/custom.js niet laden',
        'error_save_css' => 'Kon CSS niet opslaan',
        'error_save_js' => 'Kon JS niet opslaan',
        'thank_you' => 'Bedankt voor uw steun.',
        'reload' => 'Herlaad uw pagina voor volledige functionaliteiten.',
    ],
    'system' => [
        'header' => 'Systeem',
        'use_dark_mode' => 'Gebruik donkere modus voor Lychee',
        'language' => 'Taal gebruikt door Lychee',
        'nsfw_album_visibility' => 'Maak gevoelige albums standaard zichtbaar.',
        'nsfw_album_explanation' => 'Als het album openbaar is, is het nog steeds toegankelijk, maar verborgen in de weergave en <b>kan worden onthuld door op <kbd>H</kbd> te drukken</b>.',
        'cache_enabled' => 'Caching van reacties inschakelen.',
        'cache_enabled_details' => 'Dit zal de reactietijd van Lychee aanzienlijk versnellen.<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>Als u albums met wachtwoordbeveiliging gebruikt, moet u dit niet inschakelen.',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => 'Krijg exclusieve functies en ondersteun de ontwikkeling van Lychee. Ontgrendel de <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE-editie</a>.',
        'preview' => 'Voorvertoning van Lychee SE-functies inschakelen',
        'hide_call4action' => 'Verberg dit Lychee SE-registratieformulier. Ik ben tevreden met Lychee zoals het is. :)',
        'hide_warning' => 'Als dit is ingeschakeld, is de enige manier om uw licentiesleutel te registreren via het tabblad Meer hierboven. Wijzigingen worden toegepast bij het herladen van de pagina.',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => 'Om foto’s van uw Dropbox te importeren, heeft u een geldige drop-ins app-sleutel van hun website nodig.',
        'api_key' => 'Dropbox API-sleutel',
        'set_key' => 'Stel Dropbox-sleutel in',
    ],
    'gallery' => [
        'header' => 'Galerij',
        'photo_order_column' => 'Standaardkolom gebruikt voor het sorteren van foto’s',
        'photo_order_direction' => 'Standaardvolgorde gebruikt voor het sorteren van foto’s',
        'album_order_column' => 'Standaardkolom gebruikt voor het sorteren van albums',
        'album_order_direction' => 'Standaardvolgorde gebruikt voor het sorteren van albums',
        'aspect_ratio' => 'Standaardverhouding voor albumminiaturen',
        'photo_layout' => 'Indeling voor foto’s',
        'album_decoration' => 'Toon decoraties op albumhoes (sub-album en/of aantal foto’s)',
        'album_decoration_direction' => 'Lijn albumdecoraties horizontaal of verticaal uit',
        'photo_overlay' => 'Standaard overlay-informatie voor afbeeldingen',
        'license_default' => 'Standaardlicentie gebruikt voor albums',
        'license_help' => 'Hulp nodig bij het kiezen?',
    ],
    'geolocation' => [
        'header' => 'Geo-locatie',
        'map_display' => 'Toon de kaart bij gegeven GPS-coördinaten',
        'map_display_public' => 'Sta anonieme gebruikers toe de kaart te openen',
        'map_provider' => 'Bepaalt de kaartprovider',
        'map_include_subalbums' => 'Inclusief foto’s van de subalbums op de kaart',
        'location_decoding' => 'Gebruik GPS-locatiecodering',
        'location_show' => 'Toon locatie geëxtraheerd uit GPS-coördinaten',
        'location_show_public' => 'Anonieme gebruikers kunnen de geëxtraheerde locatie van GPS-coördinaten openen',
    ],
    'cssjs' => [
        'header' => 'Aangepaste CSS & Js',
        'change_css' => 'CSS wijzigen',
        'change_js' => 'JS wijzigen',
    ],
    'all' => [
        'old_setting_style' => 'Oude instellingenstijl',
        'expert_settings' => 'Expertmodus',
        'change_detected' => 'Sommige instellingen zijn gewijzigd.',
        'save' => 'Opslaan',
        'back_to_settings' => 'Terug naar gegroepeerde instellingen',
    ],
    'tool_option' => [
        'disabled' => 'uitgeschakeld',
        'enabled' => 'ingeschakeld',
        'discover' => 'ontdekken',
    ],
    'groups' => [
        'general' => 'Algemeen',
        'system' => 'Systeem',
        'modules' => 'Modules',
        'advanced' => 'Geavanceerd',
    ],
];
