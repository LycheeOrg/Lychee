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

    'title' => 'Допълнителни връзки',
    'description' => 'Управлявайте произволен, подреден списък с допълнителни връзки, показвани в навигацията и/или долния колонтитул на началната страница.',

    // Empty state
    'no_links' => 'Все още няма конфигурирани допълнителни връзки.',
    'create_first' => 'Създайте първата си връзка',

    // Table columns
    'col_label' => 'Етикет',
    'col_url' => 'URL',
    'col_placement' => 'Разположение',
    'col_enabled' => 'Активна',
    'col_actions' => 'Действия',

    // Placement labels
    'placement_nav' => 'Навигация',
    'placement_footer' => 'Долен колонтитул',
    'placement_both' => 'Навигация и долен колонтитул',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Вградена',
    'built_in_cannot_delete' => 'Тази вградена връзка не може да бъде изтрита. Вместо това я деактивирайте или я пренаредете като всяка друга връзка.',
    'built_in_url_hint' => 'Това е вградена връзка и целта ѝ не може да бъде променена.',

    // Buttons
    'create' => 'Създай връзка',
    'edit' => 'Редактирай',
    'delete' => 'Изтрий',
    'cancel' => 'Отказ',
    'save' => 'Запази',

    // Form fields
    'field_label' => 'Етикет',
    'field_label_placeholder' => 'напр. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Разположение',
    'field_open_in_new_tab' => 'Отваряне в нов раздел',
    'field_enabled' => 'Активна',

    // Modal titles
    'modal_create_title' => 'Създаване на връзка',
    'modal_edit_title' => 'Редактиране на връзка',

    // Delete confirmation
    'confirm_delete_header' => 'Изтриване на връзка',
    'confirm_delete_message' => 'Сигурни ли сте, че искате да изтриете връзката ":label"? Това действие не може да бъде отменено.',

    // Toasts
    'created' => 'Връзката е създадена успешно.',
    'updated' => 'Връзката е обновена успешно.',
    'deleted' => 'Връзката е изтрита успешно.',
    'reordered' => 'Връзките са пренаредени успешно.',
    'error_load' => 'Неуспешно зареждане на връзките.',
    'error_save' => 'Неуспешно запазване на връзката.',
    'error_delete' => 'Неуспешно изтриване на връзката.',
    'error_reorder' => 'Неуспешно пренареждане на връзките.',
];
