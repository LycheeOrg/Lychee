<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Дополнительные ссылки',
    'description' => 'Управляйте произвольным упорядоченным списком дополнительных ссылок, отображаемых в навигации и/или подвале целевой страницы.',

    // Empty state
    'no_links' => 'Дополнительные ссылки ещё не настроены.',
    'create_first' => 'Создайте свою первую ссылку',

    // Table columns
    'col_label' => 'Подпись',
    'col_url' => 'URL',
    'col_placement' => 'Размещение',
    'col_enabled' => 'Включена',
    'col_actions' => 'Действия',

    // Placement labels
    'placement_nav' => 'Навигация',
    'placement_footer' => 'Подвал',
    'placement_both' => 'Навигация и подвал',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Встроенная',
    'built_in_cannot_delete' => 'Эту встроенную ссылку нельзя удалить. Вместо этого отключите её или переместите, как любую другую ссылку.',
    'built_in_url_hint' => 'Это встроенная ссылка, её адрес нельзя изменить.',

    // Buttons
    'create' => 'Создать ссылку',
    'edit' => 'Изменить',
    'delete' => 'Удалить',
    'cancel' => 'Отмена',
    'save' => 'Сохранить',

    // Form fields
    'field_label' => 'Подпись',
    'field_label_placeholder' => 'например, Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Размещение',
    'field_open_in_new_tab' => 'Открывать в новой вкладке',
    'field_enabled' => 'Включена',

    // Modal titles
    'modal_create_title' => 'Создать ссылку',
    'modal_edit_title' => 'Изменить ссылку',

    // Delete confirmation
    'confirm_delete_header' => 'Удалить ссылку',
    'confirm_delete_message' => 'Вы уверены, что хотите удалить ссылку ":label"? Это действие нельзя отменить.',

    // Toasts
    'created' => 'Ссылка успешно создана.',
    'updated' => 'Ссылка успешно обновлена.',
    'deleted' => 'Ссылка успешно удалена.',
    'reordered' => 'Порядок ссылок успешно изменён.',
    'error_load' => 'Не удалось загрузить ссылки.',
    'error_save' => 'Не удалось сохранить ссылку.',
    'error_delete' => 'Не удалось удалить ссылку.',
    'error_reorder' => 'Не удалось изменить порядок ссылок.',
];
