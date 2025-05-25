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
    'statistics-check' => [
        'title' => 'Statistics integrity Check',
        'missing_photos' => '%d photo statistics missing.',
        'missing_albums' => '%d album statistics missing.',
        'button' => 'Create missing',
    ],
    'flush-cache' => [
        'title' => 'Очистить кэш',
        'description' => 'Очистить кэш каждого пользователя для решения проблем с устаревшими данными.',
        'button' => 'Очистить',
    ],
];
