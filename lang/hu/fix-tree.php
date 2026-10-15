<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Karbantartás',
    'intro' => 'Ezen az oldalon kézzel átrendezheti és javíthatja albumait.<br />Bármilyen módosítás előtt erősen ajánlott tájékozódni a Nested Set fastruktúrákról.',
    'warning' => 'Itt valóban tönkreteheti Lychee-telepítését, az értékeket saját felelősségére módosítsa.',
    'help' => [
        'header' => 'Súgó',
        'hover' => 'Vigye az egeret az azonosítók vagy címek fölé a kapcsolódó albumok kiemeléséhez.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Bal</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Jobb</span>',
        'convenience' => 'A kényelem érdekében a <i class="pi pi-angle-up" ></i> és <i class="pi pi-angle-down" ></i> gombokkal a %s és %s értékét +1-gyel, illetve -1-gyel módosíthatja, továbbörökítéssel.',
        'left-right-warn' => 'A <i class="text-warning-600 pi pi-chevron-circle-left" ></i> és <i class="text-warning-600 pi pi-chevron-circle-right" ></i> jelzi, hogy a %s (illetve %s) értéke valahol duplikált.',
        'parent-marked' => 'A megjelölt <span class="font-bold text-danger-600">Szülőazonosító</span> azt jelzi, hogy a %s és %s nem felel meg a Nested Set fastruktúrának. Módosítsa vagy a <span class="font-bold text-danger-600">Szülőazonosítót</span>, vagy a %s/%s értékeket.',
        'slowness' => 'Sok album esetén ez az oldal lassú lesz.',
    ],
    'buttons' => [
        'reset' => 'Visszaállítás',
        'check' => 'Ellenőrzés',
        'apply' => 'Alkalmaz',
    ],
    'no-changes' => 'Nincs alkalmazandó módosítás.',
    'table' => [
        'title' => 'Cím',
        'left' => 'Bal',
        'right' => 'Jobb',
        'id' => 'Id',
        'parent' => 'Szülőazonosító',
    ],
    'errors' => [
        'invalid' => 'Érvénytelen fa!',
        'invalid_details' => 'Ezt nem alkalmazzuk, mivel garantáltan hibás állapotot eredményezne.',
        'invalid_left' => 'A(z) %s album bal értéke érvénytelen.',
        'invalid_right' => 'A(z) %s album jobb értéke érvénytelen.',
        'invalid_left_right' => 'A(z) %s album bal/jobb értékei érvénytelenek. A bal értéknek szigorúan kisebbnek kell lennie a jobbnál: %s < %s.',
        'duplicate_left' => 'A(z) %s album bal értéke (%s) duplikált.',
        'duplicate_right' => 'A(z) %s album jobb értéke (%s) duplikált.',
        'parent' => 'A(z) %s album szülőazonosítója (%s) váratlan.',
        'unknown' => 'A(z) %s album ismeretlen hibát tartalmaz.',
    ],
];
