<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fix-tree Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenzione',
    'intro' => 'Questa pagina ti permette di riordinare e correggere manualmente i tuoi album.<br />Prima di qualsiasi modifica, ti consigliamo vivamente di informarti sulle strutture ad albero Nested Set.',
    'warning' => 'Qui puoi davvero danneggiare la tua installazione di Lychee: modifica i valori a tuo rischio.',
    'help' => [
        'header' => 'Aiuto',
        'hover' => 'Passa il mouse su ID o titoli per evidenziare gli album correlati.',
        'left' => '<span class="text-muted-color-emphasis font-bold">Sinistra</span>',
        'right' => '<span class="text-muted-color-emphasis font-bold">Destra</span>',
        'convenience' => 'Per comodità, i pulsanti <i class="pi pi-angle-up" ></i> e <i class="pi pi-angle-down" ></i> permettono di modificare i valori di %s e %s rispettivamente di +1 e -1 con propagazione.',
        'left-right-warn' => 'Le icone <i class="text-warning-600 pi pi-chevron-circle-left" ></i> e <i class="text-warning-600 pi pi-chevron-circle-right" ></i> indicano che il valore di %s (e rispettivamente %s) è duplicato da qualche parte.',
        'parent-marked' => 'Un <span class="font-bold text-danger-600">Parent Id</span> evidenziato indica che %s e %s non rispettano la struttura ad albero Nested Set. Modifica il <span class="font-bold text-danger-600">Parent Id</span> oppure i valori %s/%s.',
        'slowness' => 'Questa pagina sarà lenta con un gran numero di album.',
    ],
    'buttons' => [
        'reset' => 'Reimposta',
        'check' => 'Verifica',
        'apply' => 'Applica',
    ],
    'no-changes' => 'Nessuna modifica da applicare.',
    'table' => [
        'title' => 'Titolo',
        'left' => 'Sinistra',
        'right' => 'Destra',
        'id' => 'Id',
        'parent' => 'Parent Id',
    ],
    'errors' => [
        'invalid' => 'Albero non valido!',
        'invalid_details' => 'Non applichiamo queste modifiche perché porterebbero sicuramente a uno stato corrotto.',
        'invalid_left' => 'L\'album %s ha un valore sinistro non valido.',
        'invalid_right' => 'L\'album %s ha un valore destro non valido.',
        'invalid_left_right' => 'L\'album %s ha valori sinistro/destro non validi. Il sinistro deve essere strettamente minore del destro: %s < %s.',
        'duplicate_left' => 'L\'album %s ha un valore sinistro duplicato %s.',
        'duplicate_right' => 'L\'album %s ha un valore destro duplicato %s.',
        'parent' => 'L\'album %s ha un parent id inatteso %s.',
        'unknown' => 'L\'album %s ha un errore sconosciuto.',
    ],
];
