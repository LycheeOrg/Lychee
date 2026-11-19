<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statistics page
    |--------------------------------------------------------------------------
    */
    'title' => 'Statistik',
    'preview_text' => 'Detta är en förhandsvisning av statistiksidan som finns i Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Data som visas här är slumpmässigt genererade och speglar inte din server.',
    'no_data' => 'Användaren har inga data på servern.',
    'collapse' => 'Fäll ihop albumstorlekar',
    'total' => [
        'total' => 'Totalt',
        'albums' => 'Album',
        'photos' => 'Foton',
        'size' => 'Storlek',
    ],
    'table' => [
        'username' => 'Ägare',
        'title' => 'Titel',
        'photos' => 'Foton',
        'descendants' => 'Underalbum',
        'size' => 'Storlek',
    ],
    'punch_card' => [
        'title' => 'Aktivitet',
        'photo-taken' => '%d foton tagna',
        'photo-taken-in' => '%d foton tagna %d',
        'photo-uploaded' => '%d foton uppladdade',
        'photo-uploaded-in' => '%d foton uppladdade %d',
        'with-exif' => 'med EXIF-data',
        'less' => 'Mindre',
        'more' => 'Mer',
        'tooltip' => '%d foton den %s',
        'created_at' => 'Uppladdningsdatum',
        'taken_at' => 'EXIF-datum',
        'caption' => 'Varje kolumn motsvarar en vecka.',
    ],
    'metrics' => [
        'header' => 'Livestatistik',
        'preview_text' => 'Detta är en förhandsvisning av livestatistiken som finns i Lychee <span class="text-primary-emphasis font-bold">SE</span>. Data som visas här är slumpmässigt genererade och speglar inte din server.',
        'a_visitor' => 'En besökare',
        'visitors' => '%d besökare',
        'visit_singular' => '%1$s visade %2$s',
        'favourite_singular' => '%1$s favoritmarkerade %2$s',
        'download_singular' => '%1$s laddade ner %2$s',
        'shared_singular' => '%1$s delade %2$s',
        'visit_plural' => '%1$s visade %2$s',
        'favourite_plural' => '%1$s favoritmarkerade %2$s',
        'download_plural' => '%1$s laddade ner %2$s',
        'shared_plural' => '%1$s delade %2$s',
        'truncated' => 'Äldre aktivitet visas inte.',
        'ago' => [
            'days' => '%d dagar sedan',
            'day' => 'en dag sedan',
            'hours' => '%d timmar sedan',
            'hour' => 'en timme sedan',
            'minutes' => '%d minuter sedan',
            'few_minutes' => 'några minuter sedan',
            'seconds' => 'några sekunder sedan',
        ],
    ],
];
