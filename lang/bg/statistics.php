<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Statistics page
    |--------------------------------------------------------------------------
    */
    'title' => 'Статистика',
    'preview_text' => 'Това е визуализация на страницата със статистика, налична в Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Показаните данни са генерирани случайно и не отразяват състоянието на вашия сървър.',
    'no_data' => 'Потребителят няма данни на сървъра.',
    'collapse' => 'Свий размерите на албумите',

    'total' => [
        'total' => 'Общо',
        'albums' => 'Албуми',
        'photos' => 'Снимки',
        'size' => 'Размер',
    ],

    'table' => [
        'username' => 'Собственик',
        'title' => 'Заглавие',
        'photos' => 'Снимки',
        'descendants' => 'Подалбуми',
        'size' => 'Размер',
    ],

    'punch_card' => [
        'title' => 'Активност',
        'photo-taken' => '%d направени снимки',
        'photo-taken-in' => '%d направени снимки в %d',
        'photo-uploaded' => '%d качени снимки',
        'photo-uploaded-in' => '%d качени снимки в %d',
        'with-exif' => 'с EXIF данни',
        'less' => 'По-малко',
        'more' => 'Повече',
        'tooltip' => '%d снимки на %s',
        'created_at' => 'Дата на качване',
        'taken_at' => 'Дата на заснемане',
        'caption' => 'Всяка колона представя седмица.',
    ],

    'metrics' => [
        'header' => 'Live метрики',
        'preview_text' => 'Това е визуализация на live метриките, налични в Lychee <span class="text-primary-emphasis font-bold">SE</span>. Показаните данни са генерирани случайно и не отразяват сървъра ви.',
        'a_visitor' => 'Посетител',
        'visitors' => '%d посетители',
        'visit_singular' => '%1$s е видял %2$s',
        'favourite_singular' => '%1$s е добавил в любими %2$s',
        'download_singular' => '%1$s е изтеглил %2$s',
        'shared_singular' => '%1$s е споделил %2$s',
        'visit_plural' => '%1$s са видели %2$s',
        'favourite_plural' => '%1$s са добавили в любими %2$s',
        'download_plural' => '%1$s са изтеглили %2$s',
        'shared_plural' => '%1$s са споделили %2$s',

        'ago' => [
            'days' => '%d дни преди',
            'day' => 'преди ден',
            'hours' => '%d часа преди',
            'hour' => 'преди час',
            'minutes' => '%d минути преди',
            'few_minutes' => 'преди няколко минути',
            'seconds' => 'преди няколко секунди',
        ],
    ],
];
