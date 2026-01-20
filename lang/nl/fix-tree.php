<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Onderhoud',
    'intro' => 'Deze pagina stelt u in staat om uw albums handmatig opnieuw te ordenen en te corrigeren.<br />Voor u wijzigingen aanbrengt, raden we u sterk aan om te lezen over Nested Set boomstructuren.',
    'warning' => 'U kunt hier echt uw Lychee-installatie kapot maken, wijzig waarden op eigen risico.',
    'help' => [
        'header' => 'Hulp',
        'hover' => 'Beweeg met de muis over id’s of titels om gerelateerde albums te markeren.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Links</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Rechts</span>',
        'convenience' => 'Voor uw gemak kunt u met de knoppen <i class="pi pi-angle-up" ></i> en <i class="pi pi-angle-down" ></i> de waarden van %s en %s respectievelijk met +1 en -1 wijzigen met propagatie.',
        'left-right-warn' => 'De <i class="text-warning-600 pi pi-chevron-circle-left" ></i> en <i class="text-warning-600 pi pi-chevron-circle-right" ></i> geven aan dat de waarde van %s (en respectievelijk %s) ergens is gedupliceerd.',
        'parent-marked' => 'Gemarkeerde <span class="font-bold text-danger-600">Ouder Id</span> geeft aan dat de %s en %s niet voldoen aan de Nested Set boomstructuren. Wijzig ofwel de <span class="font-bold text-danger-600">Ouder Id</span> of de %s/%s waarden.',
        'slowness' => 'Deze pagina zal traag zijn met een groot aantal albums.',
    ],
    'buttons' => [
        'reset' => 'Resetten',
        'check' => 'Controleren',
        'apply' => 'Toepassen',
    ],
    'no-changes' => 'Geen wijzigingen om toe te passen.',
    'table' => [
        'title' => 'Titel',
        'left' => 'Links',
        'right' => 'Rechts',
        'id' => 'Id',
        'parent' => 'Ouder Id',
    ],
    'errors' => [
        'invalid' => 'Ongeldige boom!',
        'invalid_details' => 'We passen dit niet toe omdat het gegarandeerd een gebroken staat is.',
        'invalid_left' => 'Album %s heeft een ongeldige linkerwaarde.',
        'invalid_right' => 'Album %s heeft een ongeldige rechterwaarde.',
        'invalid_left_right' => 'Album %s heeft ongeldige linker/rechterwaarden. Links moet strikt kleiner zijn dan rechts: %s < %s.',
        'duplicate_left' => 'Album %s heeft een dubbele linkerwaarde %s.',
        'duplicate_right' => 'Album %s heeft een dubbele rechterwaarde %s.',
        'parent' => 'Album %s heeft een onverwachte ouder id %s.',
        'unknown' => 'Album %s heeft een onbekende fout.',
    ],
];
