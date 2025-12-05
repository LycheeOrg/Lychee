<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Wartung',
    'intro' => 'Auf dieser Seite können Alben manuell neu angeordnet und korrigiert werden.<br />Bevor Änderungen vorgenommen werden, empfehlen wir dringend, sich über verschachtelte Baumstruktur zu informieren.',
    'warning' => 'Die Lychee-Installation kann hier wirklich zerstört werden. Ändern der Werte auf eigene Gefahr.',
    'help' => [
        'header' => 'Hilfe',
        'hover' => 'Bewegen Sie den Mauszeiger über Namen oder Titel, um verwandte Alben hervorzuheben.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Links</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Rechts</span>',
        'convenience' => 'Die Schaltflächen <i class="pi pi-angle-up" ></i> und <i class="pi pi-angle-down" ></i> ermöglichen es, die Werte von %s und %s um +1 bzw. -1 zu ändern und weiterzugeben.',
        'left-right-warn' => 'Das <i class="text-warning-600 pi pi-chevron-circle-left" ></i> und <i class="text-warning-600 pi pi-chevron-circle-right" ></i> zeigt an, dass der Wert von %s (bzw. %s) irgendwo doppelt vorhanden ist.',
        'parent-marked' => 'Die Markierung <span class="font-bold text-danger-600">Parent Id</span> zeigt an, dass die %s und %s nicht den verschachtelten Baumstrukturen entsprechen. Die <span class="font-bold text-danger-600">Parent Id</span> oder die %s/%s Werte sollten bearbeitet werden.',
        'slowness' => 'Diese Seite wird bei einer großen Anzahl von Alben sehr langsam sein.',
    ],
    'buttons' => [
        'reset' => 'Zurücksetzen',
        'check' => 'Prüfen',
        'apply' => 'Anwenden',
    ],
    'table' => [
        'title' => 'Titel',
        'left' => 'Links',
        'right' => 'Rechts',
        'id' => 'ID',
        'parent' => 'Übergeordnete ID',
    ],
    'errors' => [
        'invalid' => 'Ungültiger Baum!',
        'invalid_details' => 'Wir wenden dies nicht an, da es sich garantiert um einen defekten Zustand handelt.',
        'invalid_left' => 'Album %s hat einen ungültigen linken Wert.',
        'invalid_right' => 'Album %s hat einen ungültigen Wert.',
        'invalid_left_right' => 'Album %s hat einen ungültigen Links/Rechts-Wert. Links sollte deutlich kleiner sein als rechts: %s < %s.',
        'duplicate_left' => 'Album %s hat einen doppelten Wert %s.',
        'duplicate_right' => 'Album %s hat einen doppelten berechneten Wert %s.',
        'parent' => 'Album %s hat eine unerwartete übergeordnete ID %s.',
        'unknown' => 'Album %s hat einen unbekannten Fehler.',
    ],
];
