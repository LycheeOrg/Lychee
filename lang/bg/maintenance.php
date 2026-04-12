<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Maintenance Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Поддръжка',
    'description' => 'На тази страница ще намерите всички необходими действия, за да поддържате инсталацията на Lychee да работи гладко и без проблеми.',
    'cleaning' => [
        'title' => 'Почистване на %s',
        'result' => '%s изтрити.',
        'description' => 'Премахни всички съдържания от <span class="font-mono">%s</span>',
        'button' => 'Почисти',
    ],
    'duplicate-finder' => [
        'title' => 'Дубликати',
        'description' => 'Този модул отчита потенциалните дубликати между снимките.',
        'duplicates-all' => 'Дубликати във всички албуми',
        'duplicates-title' => 'Дубликати по заглавие на албум',
        'duplicates-per-album' => 'Дубликати на албум',
        'show' => 'Покажи дубликатите',
        'load' => 'Зареди броя',
    ],
    'fix-jobs' => [
        'title' => 'Поправяне на историята на задачите',
        'description' => 'Маркирай задачите със статус <span class="text-ready-400">%s</span> или <span class="text-primary-500">%s</span> като <span class="text-danger-700">%s</span>.',
        'button' => 'Поправи историята на задачите',
    ],
    'gen-sizevariants' => [
        'title' => 'Липсващи %s',
        'description' => 'Намерени са %d %s, които могат да бъдат генерирани.',
        'button' => 'Генерирай!',
        'success' => 'Успешно генерирани %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Липсващи размери на файлове',
        'description' => 'Намерени са %d малки варианта без информация за размер.',
        'button' => 'Вземи данни!',
        'success' => 'Успешно изчислени размерите на %d малки варианта.',
    ],
    'fix-tree' => [
        'title' => 'Статистика на дървото',
        'Oddness' => 'Нередности',
        'Duplicates' => 'Дубликати',
        'Wrong parents' => 'Грешни родители',
        'Missing parents' => 'Липсващи родители',
        'button' => 'Поправи дървото',
    ],
    'optimize' => [
        'title' => 'Оптимизирай базата данни',
        'description' => 'Ако забележите забавяне в инсталацията, това може да се дължи на липсващи индекси в базата данни.',
        'button' => 'Оптимизирай базата данни',
    ],
    'update' => [
        'title' => 'Актуализации',
        'check-button' => 'Провери за актуализации',
        'update-button' => 'Актуализирай',
        'no-pending-updates' => 'Няма налични актуализации.',
    ],
    'missing-palettes' => [
        'title' => 'Липсващи палитри',
        'description' => 'Намерени са %d липсващи палитри.',
        'button' => 'Създай липсващите',
    ],
    'statistics-check' => [
        'title' => 'Проверка на целостта на статистиката',
        'missing_photos' => 'Липсва статистика за %d снимки.',
        'missing_albums' => 'Липсва статистика за %d албуми.',
        'button' => 'Създай липсващите',
    ],
    'flush-cache' => [
        'title' => 'Изчисти кеша',
        'description' => 'Изчисти кеша на всеки потребител, за да се решат проблеми с валидността.',
        'button' => 'Изчисти',
    ],
    'old-orders' => [
        'title' => 'Старо поръчки',
        'description' => 'Намерени са %d стари поръчки.<br/><br/>Стара поръчка е по-стара от 14 дни, няма свързан потребител и все още е в очакване на плащане или не съдържа артикули.',
        'button' => 'Изтрий старите поръчки',
    ],
    'fulfill-orders' => [
        'title' => 'Поръчки за изпълнение',
        'description' => 'Намерени са %d поръчки със съдържание, което не е направено достъпно.<br/><br/>Кликнете бутона, за да разпределите съдържанието, когато е възможно.',
        'button' => 'Изпълни поръчките',
    ],
    'fulfill-precompute' => [
        'title' => 'Предварително изчислени полета на албума',
        'description' => 'Намерени са %d албума с липсващи предварително изчислени полета.<br/><br/>Еквивалентно на изпълнение на: php artisan lychee:backfill-album-fields',
        'button' => 'Изчисли полетата',
    ],
    'flush-queue' => [
        'title' => 'Изчисти опашката',
        'description' => 'Намерени са %d чакащи задачи в опашката.<br/><br/>ВНИМАНИЕ: Изчистването на опашката ще изтрие завинаги всички чакащи задачи. Това не може да бъде отменено.',
        'button' => 'Изчисти опашката',
    ],
    'backfill-album-sizes' => [
        'title' => 'Статистика за размера на албумите',
        'description' => 'Открити са %d албума без статистика за размера.<br/><br/>Еквивалентно на изпълнението на: php artisan lychee:recompute-album-sizes',
        'button' => 'Изчисли размерите',
    ],

    'face_quality' => [
        'title' => 'Face Quality Review',
        'description' => 'Review face detections by quality score and dismiss low-quality or erroneous faces.',
        'sort_by' => 'Sort by:',
        'sort_confidence' => 'Confidence',
        'sort_blur' => 'Blur (Laplacian)',
        'no_faces' => 'No qualifying faces. Everything looks good!',
        'col_face' => 'Face',
        'col_person' => 'Person',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Confidence',
        'col_blur' => 'Blur Score',
        'col_actions' => 'Actions',
        'unassigned' => 'Unassigned',
        'dismiss' => 'Dismiss face',
        'load_error' => 'Failed to load faces.',
        'dismissed' => 'Face dismissed.',
        'dismiss_error' => 'Failed to dismiss face.',
        'batch_dismiss' => 'Dismiss selected',
        'batch_dismissed' => ':count face(s) dismissed.',
        'batch_dismiss_error' => 'Failed to dismiss selected faces.',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
        'selected_count' => ':count selected',
    ],
    'bulk-scan-faces' => [
        'description' => 'Found %d photos that have not yet been scanned for facial recognition.<br/><br/>Requires the AI Vision service to be running.',
    ],
    'run-clustering' => [
        'description' => 'Trigger face clustering in the AI Vision service. Groups detected faces by similarity so you can assign them to people.',
        'success' => 'Clustering started successfully.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Destroy Dismissed Faces',
        'description' => 'Found %d dismissed faces. Destroying them will permanently delete their crop files and embeddings.',
        'action' => 'Destroy All',
        'success' => 'Dismissed faces destroyed successfully.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sync Face Embeddings',
        'description' => 'Face count mismatch detected (%d difference). Syncing will pull latest face data from AI Vision service to Lychee.',
        'action' => 'Sync Now',
        'success' => 'Face embeddings synchronized successfully.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Reset Face Scan Status',
        'description' => 'Found %d photos with a stuck-pending or failed face scan status. Resetting them will allow them to be re-scanned.',
        'action' => 'Reset All',
        'success' => 'Face scan statuses reset successfully.',
    ],

    ];
