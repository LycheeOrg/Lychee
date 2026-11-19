<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statistics page
    |--------------------------------------------------------------------------
    */
    'title' => 'Statistiky',
    'preview_text' => 'Toto je náhled stránky statistik dostupné v Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Zobrazená data jsou náhodně generovaná a neodrážejí váš server.',
    'no_data' => 'Uživatel nemá na serveru žádná data.',
    'collapse' => 'Sbalit velikosti alb',
    'total' => [
        'total' => 'Celkem',
        'albums' => 'Alba',
        'photos' => 'Fotografie',
        'size' => 'Velikost',
    ],
    'table' => [
        'username' => 'Vlastník',
        'title' => 'Název',
        'photos' => 'Fotografie',
        'descendants' => 'Podřízené',
        'size' => 'Velikost',
    ],
    'punch_card' => [
        'title' => 'Aktivita',
        'photo-taken' => 'Pořízeno fotografií: %d',
        'photo-taken-in' => 'Pořízeno fotografií: %d v roce %d',
        'photo-uploaded' => 'Nahráno fotografií: %d',
        'photo-uploaded-in' => 'Nahráno fotografií: %d v roce %d',
        'with-exif' => 's daty EXIF',
        'less' => 'Méně',
        'more' => 'Více',
        'tooltip' => 'Fotografií: %d dne %s',
        'created_at' => 'Datum nahrání',
        'taken_at' => 'Datum EXIF',
        'caption' => 'Každý sloupec představuje jeden týden.',
    ],
    'metrics' => [
        'header' => 'Živé metriky',
        'preview_text' => 'Toto je náhled živých metrik dostupných v Lychee <span class="text-primary-emphasis font-bold">SE</span>. Zobrazená data jsou náhodně generovaná a neodrážejí váš server.',
        'a_visitor' => 'Návštěvník',
        'visitors' => 'Návštěvníci (%d)',
        'visit_singular' => '%1$s zobrazil %2$s',
        'favourite_singular' => '%1$s přidal %2$s do oblíbených',
        'download_singular' => '%1$s stáhl %2$s',
        'shared_singular' => '%1$s sdílel %2$s',
        'visit_plural' => '%1$s zobrazili %2$s',
        'favourite_plural' => '%1$s přidali %2$s do oblíbených',
        'download_plural' => '%1$s stáhli %2$s',
        'shared_plural' => '%1$s sdíleli %2$s',
        'truncated' => 'Starší aktivita není zobrazena.',
        'ago' => [
            'days' => 'před %d dny',
            'day' => 'před dnem',
            'hours' => 'před %d hodinami',
            'hour' => 'před hodinou',
            'minutes' => 'před %d minutami',
            'few_minutes' => 'před několika minutami',
            'seconds' => 'před několika sekundami',
        ],
    ],
];
