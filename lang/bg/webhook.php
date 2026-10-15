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
    'description' => 'Конфигурирайте изходящи webhooks, които се задействат при добавяне, преместване или изтриване на снимки.',

    // Empty state
    'no_webhooks' => 'Все още няма конфигурирани webhooks.',
    'create_first' => 'Създайте първия си webhook',

    // Table columns
    'col_name' => 'Име',
    'col_event' => 'Събитие',
    'col_method' => 'Метод',
    'col_url' => 'URL',
    'col_format' => 'Формат',
    'col_enabled' => 'Активен',
    'col_actions' => 'Действия',

    // Event labels
    'event_photo_add' => 'Добавена снимка',
    'event_photo_move' => 'Преместена снимка',
    'event_photo_delete' => 'Изтрита снимка',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query String',

    // Buttons
    'create' => 'Създай webhook',
    'edit' => 'Редактирай',
    'delete' => 'Изтрий',
    'cancel' => 'Отказ',
    'save' => 'Запази',

    // Form fields
    'field_name' => 'Име',
    'field_name_placeholder' => 'напр. Моят webhook',
    'field_event' => 'Събитие',
    'field_method' => 'HTTP метод',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Формат на данните (payload)',
    'field_enabled' => 'Активен',
    'field_secret' => 'Тайна',
    'field_secret_placeholder' => 'Оставете празно, за да запазите съществуващата тайна',
    'field_secret_header' => 'Заглавка за тайната',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Изпращай ID на снимката',
    'field_send_album_id' => 'Изпращай ID на албума',
    'field_send_title' => 'Изпращай заглавието',
    'field_send_size_variants' => 'Изпращай размерните варианти',

    // Modal titles
    'modal_create_title' => 'Създаване на webhook',
    'modal_edit_title' => 'Редактиране на webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Изтриване на webhook',
    'confirm_delete_message' => 'Сигурни ли сте, че искате да изтриете webhook ":name"? Това действие не може да бъде отменено.',
    'delete_warning' => 'Това действие не може да бъде отменено.',

    // Toasts
    'created' => 'Webhook е създаден успешно.',
    'updated' => 'Webhook е обновен успешно.',
    'deleted' => 'Webhook е изтрит успешно.',
    'error_load' => 'Неуспешно зареждане на webhooks.',
    'error_save' => 'Неуспешно запазване на webhook.',
    'error_delete' => 'Неуспешно изтриване на webhook.',

    // Secret badge
    'has_secret' => 'Зададена тайна',
    'no_secret' => 'Без тайна',
];
