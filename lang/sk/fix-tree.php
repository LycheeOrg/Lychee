<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Údržba',
    'intro' => 'Táto stránka vám umožňuje ručne preusporiadať a opraviť albumy.<br />Pred akýmikoľvek úpravami dôrazne odporúčame prečítať si o stromových štruktúrach Nested Set.',
    'warning' => 'Tu môžete naozaj poškodiť svoju inštaláciu Lychee, hodnoty upravujete na vlastné riziko.',
    'help' => [
        'header' => 'Pomoc',
        'hover' => 'Prejdite myšou nad ID alebo názvami na zvýraznenie súvisiacich albumov.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Ľavá</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Pravá</span>',
        'convenience' => 'Pre vaše pohodlie tlačidlá <i class="pi pi-angle-up" ></i> a <i class="pi pi-angle-down" ></i> umožňujú zmeniť hodnoty %s a %s o +1, resp. -1 s propagáciou.',
        'left-right-warn' => 'Ikony <i class="text-warning-600 pi pi-chevron-circle-left" ></i> a <i class="text-warning-600 pi pi-chevron-circle-right" ></i> označujú, že hodnota %s (resp. %s) je niekde duplicitná.',
        'parent-marked' => 'Označené <span class="font-bold text-danger-600">Parent Id</span> znamená, že %s a %s nespĺňajú stromovú štruktúru Nested Set. Upravte buď <span class="font-bold text-danger-600">Parent Id</span>, alebo hodnoty %s/%s.',
        'slowness' => 'Pri veľkom počte albumov bude táto stránka pomalá.',
    ],
    'buttons' => [
        'reset' => 'Obnoviť',
        'check' => 'Skontrolovať',
        'apply' => 'Použiť',
    ],
    'no-changes' => 'Žiadne zmeny na použitie.',
    'table' => [
        'title' => 'Názov',
        'left' => 'Ľavá',
        'right' => 'Pravá',
        'id' => 'Id',
        'parent' => 'ID rodiča',
    ],
    'errors' => [
        'invalid' => 'Neplatný strom!',
        'invalid_details' => 'Toto nepoužijeme, pretože by to zaručene viedlo k poškodenému stavu.',
        'invalid_left' => 'Album %s má neplatnú ľavú hodnotu.',
        'invalid_right' => 'Album %s má neplatnú pravú hodnotu.',
        'invalid_left_right' => 'Album %s má neplatné ľavé/pravé hodnoty. Ľavá musí byť ostro menšia ako pravá: %s < %s.',
        'duplicate_left' => 'Album %s má duplicitnú ľavú hodnotu %s.',
        'duplicate_right' => 'Album %s má duplicitnú pravú hodnotu %s.',
        'parent' => 'Album %s má neočakávané ID rodiča %s.',
        'unknown' => 'Album %s má neznámu chybu.',
    ],
];
