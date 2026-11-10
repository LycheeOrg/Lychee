<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Настройте исходящие вебхуки, срабатывающие при добавлении, перемещении или удалении фотографий.',

    // Empty state
    'no_webhooks' => 'Вебхуки ещё не настроены.',
    'create_first' => 'Создайте свой первый вебхук',

    // Table columns
    'col_name' => 'Название',
    'col_event' => 'Событие',
    'col_method' => 'Метод',
    'col_url' => 'URL',
    'col_format' => 'Формат',
    'col_enabled' => 'Включён',
    'col_actions' => 'Действия',

    // Event labels
    'event_photo_add' => 'Фото добавлено',
    'event_photo_move' => 'Фото перемещено',
    'event_photo_delete' => 'Фото удалено',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Строка запроса',

    // Buttons
    'create' => 'Создать вебхук',
    'edit' => 'Изменить',
    'delete' => 'Удалить',
    'cancel' => 'Отмена',
    'save' => 'Сохранить',

    // Form fields
    'field_name' => 'Название',
    'field_name_placeholder' => 'например, Мой вебхук',
    'field_event' => 'Событие',
    'field_method' => 'Метод HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Формат полезной нагрузки',
    'field_enabled' => 'Включён',
    'field_secret' => 'Секрет',
    'field_secret_placeholder' => 'Оставьте пустым, чтобы сохранить текущий секрет',
    'field_secret_header' => 'Заголовок секрета',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Отправлять ID фото',
    'field_send_album_id' => 'Отправлять ID альбома',
    'field_send_title' => 'Отправлять название',
    'field_send_size_variants' => 'Отправлять варианты размеров',

    // Modal titles
    'modal_create_title' => 'Создать вебхук',
    'modal_edit_title' => 'Изменить вебхук',

    // Delete confirmation
    'confirm_delete_header' => 'Удалить вебхук',
    'confirm_delete_message' => 'Вы уверены, что хотите удалить вебхук ":name"? Это действие нельзя отменить.',
    'delete_warning' => 'Это действие нельзя отменить.',

    // Toasts
    'created' => 'Вебхук успешно создан.',
    'updated' => 'Вебхук успешно обновлён.',
    'deleted' => 'Вебхук успешно удалён.',
    'error_load' => 'Не удалось загрузить вебхуки.',
    'error_save' => 'Не удалось сохранить вебхук.',
    'error_delete' => 'Не удалось удалить вебхук.',

    // Secret badge
    'has_secret' => 'Секрет задан',
    'no_secret' => 'Без секрета',
];
