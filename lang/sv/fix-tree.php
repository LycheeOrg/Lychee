<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Underhåll',
    'intro' => 'Denna sida låter dig ordna om och reparera dina album manuellt.<br />Innan du gör några ändringar rekommenderar vi starkt att du läser på om Nested Set-trädstrukturer.',
    'warning' => 'Du kan verkligen förstöra din Lychee-installation här, ändra värden på egen risk.',
    'help' => [
        'header' => 'Hjälp',
        'hover' => 'Håll muspekaren över ID-nummer eller titlar för att markera relaterade album.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Vänster</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Höger</span>',
        'convenience' => 'För enkelhetens skull låter knapparna <i class="pi pi-angle-up" ></i> och <i class="pi pi-angle-down" ></i> dig ändra värdena för %s och %s med +1 respektive -1 med spridning.',
        'left-right-warn' => '<i class="text-warning-600 pi pi-chevron-circle-left" ></i> och <i class="text-warning-600 pi pi-chevron-circle-right" ></i> anger att värdet för %s (respektive %s) förekommer dubbelt någonstans.',
        'parent-marked' => 'Markerat <span class="font-bold text-danger-600">Förälder-ID</span> anger att %s och %s inte uppfyller Nested Set-trädstrukturen. Redigera antingen <span class="font-bold text-danger-600">Förälder-ID</span> eller värdena för %s/%s.',
        'slowness' => 'Denna sida blir långsam med ett stort antal album.',
    ],
    'buttons' => [
        'reset' => 'Återställ',
        'check' => 'Kontrollera',
        'apply' => 'Verkställ',
    ],
    'no-changes' => 'Inga ändringar att verkställa.',
    'table' => [
        'title' => 'Titel',
        'left' => 'Vänster',
        'right' => 'Höger',
        'id' => 'Id',
        'parent' => 'Förälder-ID',
    ],
    'errors' => [
        'invalid' => 'Ogiltigt träd!',
        'invalid_details' => 'Vi verkställer inte detta eftersom det garanterat leder till ett trasigt tillstånd.',
        'invalid_left' => 'Album %s har ett ogiltigt vänstervärde.',
        'invalid_right' => 'Album %s har ett ogiltigt högervärde.',
        'invalid_left_right' => 'Album %s har ogiltiga vänster-/högervärden. Vänster måste vara strikt mindre än höger: %s < %s.',
        'duplicate_left' => 'Album %s har ett dubblerat vänstervärde %s.',
        'duplicate_right' => 'Album %s har ett dubblerat högervärde %s.',
        'parent' => 'Album %s har ett oväntat förälder-ID %s.',
        'unknown' => 'Album %s har ett okänt fel.',
    ],
];
