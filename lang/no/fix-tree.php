<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Vedlikehold',
    'intro' => 'Denne siden lar deg endre rekkefølgen og fikse albumene dine manuelt.<br />Før du foretar endringer, anbefaler vi på det sterkeste at du leser om trestrukturer for nestede sett.',
    'warning' => 'Du kan virkelig ødelegge Lychee-installasjonen din her, endre verdier på egen risiko.',
    'help' => [
        'header' => 'Hjelp',
        'hover' => 'Hold musepekeren over id-er eller titler for å utheve tilhørende album.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Venstre</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Høyre</span>',
        'convenience' => 'For enkelhets skyld lar knappene <i class="pi pi-angle-up" ></i> og <i class="pi pi-angle-down" ></i> deg endre verdiene til %s og %s med henholdsvis +1 og -1 med propagering.',
        'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> og <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indikerer at verdien til %s (og henholdsvis %s) er duplisert et sted.',
        'parent-marked' => 'Merket <span class="font-bold text-danger-600">Parent Id</span> indikerer at %s og %s ikke tilfredsstiller Nest Set-tre-strukturene. Rediger enten <span class="font-bold text-danger-600">Parent Id</span> eller verdiene %s/%s.',
        'slowness' => 'Denne siden vil være treg med et stort antall album.',
    ],
    'buttons' => [
        'reset' => 'Tilbakestill',
        'check' => 'Sjekk',
        'apply' => 'Bruk',
    ],
    'no-changes' => 'Ingen endringer å bruke.',
    'table' => [
        'title' => 'Tittel',
        'left' => 'Venstre',
        'right' => 'Høyre',
        'id' => 'Id',
        'parent' => 'Parent Id',
    ],
    'errors' => [
        'invalid' => 'Ugyldig tre!',
        'invalid_details' => 'Vi bruker ikke dette siden det er garantert å være en ødelagt tilstand.',
        'invalid_left' => 'Album %s har en ugyldig venstreverdi.',
        'invalid_right' => 'Album %s har en ugyldig høyreverdi.',
        'invalid_left_right' => 'Album %s har ugyldige venstre-/høyreverdier. Venstre skal være strengt mindre enn høyre: %s < %s.',
        'duplicate_left' => 'Album %s har en duplisert venstreverdi %s.',
        'duplicate_right' => 'Album %s har en duplisert høyreverdi %s.',
        'parent' => 'Album %s har en uventet foreldre-id %s.',
        'unknown' => 'Album %s har en ukjent feil.',
    ],
];
