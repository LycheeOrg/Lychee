<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Обслуживание',
    'description' => 'На этой странице вы найдете все необходимые действия для поддержания вашей установки Lychee в рабочем и исправном состоянии.',
    'cleaning' => [
        'title' => 'Очистка %s',
        'result' => '%s удалено.',
        'description' => 'Удалить все содержимое из <span class="font-mono">%s</span>',
        'button' => 'Очистить',
    ],
    'duplicate-finder' => [
        'title' => 'Дубликаты',
        'description' => 'Этот модуль подсчитывает возможные дубликаты среди изображений.',
        'duplicates-all' => 'Дубликаты по всем альбомам',
        'duplicates-title' => 'Дубликаты по заголовкам альбомов',
        'duplicates-per-album' => 'Дубликаты по альбомам',
        'show' => 'Показать дубликаты',
        'load' => 'Load counts',
    ],
    'fix-jobs' => [
        'title' => 'Исправление истории задач',
        'description' => 'Пометить задачи со статусом <span class="text-ready-400">%s</span> или <span class="text-primary-500">%s</span> как <span class="text-danger-700">%s</span>.',
        'button' => 'Исправить историю задач',
    ],
    'gen-sizevariants' => [
        'title' => 'Отсутствуют %s',
        'description' => 'Найдено %d %s, которые могут быть сгенерированы.',
        'button' => 'Генерировать!',
        'success' => 'Успешно сгенерировано %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Отсутствуют размеры файлов',
        'description' => 'Найдено %d маленьких вариантов без размера файла.',
        'button' => 'Получить данные!',
        'success' => 'Успешно вычислены размеры %d маленьких вариантов.',
    ],
    'fix-tree' => [
        'title' => 'Статистика дерева',
        'Oddness' => 'Необычности',
        'Duplicates' => 'Дубликаты',
        'Wrong parents' => 'Неверные родители',
        'Missing parents' => 'Отсутствующие родители',
        'button' => 'Исправить дерево',
    ],
    'optimize' => [
        'title' => 'Оптимизация базы данных',
        'description' => 'Если вы замечаете замедление работы установки, возможно, это связано с отсутствием необходимых индексов в базе данных.',
        'button' => 'Оптимизировать базу данных',
    ],
    'update' => [
        'title' => 'Обновления',
        'check-button' => 'Проверить обновления',
        'update-button' => 'Обновить',
        'no-pending-updates' => 'Нет ожидающих обновлений.',
    ],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Statistics integrity Check',
        'missing_photos' => 'Для %d фото нет статистики.',
        'missing_albums' => 'Для %d альбомов нет статистики.',
        'button' => 'Create missing',
    ],
    'flush-cache' => [
        'title' => 'Очистить кэш',
        'description' => 'Очистить кэш каждого пользователя для решения проблем с устаревшими данными.',
        'button' => 'Очистить',
    ],
    'old-orders' => [
        'title' => 'Old Orders',
        'description' => 'Found %d old orders.<br/><br/>An old order is older than 14 days, that have no associated user and are either still pending payment or have no items in them.',
        'button' => 'Delete old orders',
    ],
    'fulfill-orders' => [
        'title' => 'Orders to fulfill',
        'description' => 'Found %d orders with content that has not been made available.<br/><br/>Click on the button to assign content when possible.',
        'button' => 'Fulfill orders',
    ],
    'fulfill-precompute' => [
        'title' => 'Album Precomputed Fields',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-fields',
        'button' => 'Compute fields',
    ],
    'flush-queue' => [
        'title' => 'Flush Queue',
        'description' => 'Found %d pending jobs in the queue.<br/><br/>CAUTION: Clearing the queue will permanently delete all pending jobs. This cannot be undone.',
        'button' => 'Clear queue',
    ],
    'backfill-album-sizes' => [
        'title' => 'Album Size Statistics',
        'description' => 'Found %d albums without size statistics.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-sizes',
        'button' => 'Compute sizes',
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
