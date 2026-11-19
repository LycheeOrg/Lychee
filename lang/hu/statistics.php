<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Statistics page
    |--------------------------------------------------------------------------
    */
    'title' => 'Statisztikák',
    'preview_text' => 'Ez a Lychee <span class="text-primary-emphasis font-bold">SE</span> statisztikai oldalának előnézete.<br />Az itt látható adatok véletlenszerűen generáltak, és nem a szerverét tükrözik.',
    'no_data' => 'A felhasználónak nincsenek adatai a szerveren.',
    'collapse' => 'Albumméretek összecsukása',
    'total' => [
        'total' => 'Összesen',
        'albums' => 'Albumok',
        'photos' => 'Fényképek',
        'size' => 'Méret',
    ],
    'table' => [
        'username' => 'Tulajdonos',
        'title' => 'Cím',
        'photos' => 'Fényképek',
        'descendants' => 'Gyermekek',
        'size' => 'Méret',
    ],
    'punch_card' => [
        'title' => 'Aktivitás',
        'photo-taken' => '%d fénykép készült',
        'photo-taken-in' => '%d fénykép készült ebben az évben: %d',
        'photo-uploaded' => '%d fénykép feltöltve',
        'photo-uploaded-in' => '%d fénykép feltöltve ebben az évben: %d',
        'with-exif' => 'EXIF-adatokkal',
        'less' => 'Kevesebb',
        'more' => 'Több',
        'tooltip' => '%d fénykép ekkor: %s',
        'created_at' => 'Feltöltés dátuma',
        'taken_at' => 'EXIF-dátum',
        'caption' => 'Minden oszlop egy hetet jelöl.',
    ],
    'metrics' => [
        'header' => 'Élő metrikák',
        'preview_text' => 'Ez a Lychee <span class="text-primary-emphasis font-bold">SE</span> élő metrikáinak előnézete. Az itt látható adatok véletlenszerűen generáltak, és nem a szerverét tükrözik.',
        'a_visitor' => 'Egy látogató',
        'visitors' => '%d látogató',
        'visit_singular' => '%1$s megtekintette: %2$s',
        'favourite_singular' => '%1$s kedvencnek jelölte: %2$s',
        'download_singular' => '%1$s letöltötte: %2$s',
        'shared_singular' => '%1$s megosztotta: %2$s',
        'visit_plural' => '%1$s megtekintette: %2$s',
        'favourite_plural' => '%1$s kedvencnek jelölte: %2$s',
        'download_plural' => '%1$s letöltötte: %2$s',
        'shared_plural' => '%1$s megosztotta: %2$s',
        'truncated' => 'A régebbi aktivitás nem látható.',
        'ago' => [
            'days' => '%d napja',
            'day' => 'egy napja',
            'hours' => '%d órája',
            'hour' => 'egy órája',
            'minutes' => '%d perce',
            'few_minutes' => 'néhány perce',
            'seconds' => 'néhány másodperce',
        ],
    ],
];
