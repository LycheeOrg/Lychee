<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Групова редакция на албуми',
    'description' => 'Редактирайте метаданните и настройките за видимост на няколко албума наведнъж.',
    'warning' => 'Промените тук се прилагат веднага и не могат да бъдат отменени. Албумите с етикети не се показват.',

    // Table columns
    'col_title' => 'Заглавие',
    'col_owner' => 'Собственик',
    'col_license' => 'Лиценз',
    'col_is_nsfw' => 'Чувствителен',
    'col_is_public' => 'Публичен',
    'col_is_link_required' => 'Връзка',
    'col_grants_full_photo_access' => 'Пълна снимка',
    'col_grants_download' => 'Изтегляне',
    'col_grants_upload' => 'Качване',
    'col_photo_sorting' => 'Сорт. снимки',
    'col_album_sorting' => 'Сорт. албуми',
    'col_created_at' => 'Създаден',

    // Filter
    'filter_placeholder' => 'Търсене по заглавие...',

    // Pagination
    'per_page' => 'На страница',
    'total_selected' => 'Избран е :n албум|Избрани са :n албума',
    'select_all_page' => 'Избиране на всички на тази страница',
    'select_all_matching' => 'Избиране на всички съвпадащи',
    'cap_warning' => 'Избрани са само първите 1 000 албума.',

    // Mode toggle
    'mode_paginated' => 'По страници',
    'mode_infinite' => 'Безкрайно превъртане',

    // Action buttons
    'action_delete' => 'Изтриване',
    'action_set_owner' => 'Задаване на собственик',
    'action_edit_fields' => 'Редактиране на полета',

    // Edit Fields modal
    'edit_fields_title' => 'Редактиране на полета',
    'edit_fields_description' => 'Ще бъдат обновени само отметнатите полета. Празните стойности изчистват полето.',
    'section_metadata' => 'Метаданни',
    'section_visibility' => 'Видимост',
    'field_description' => 'Описание',
    'field_copyright' => 'Авторско право',
    'field_license' => 'Лиценз',
    'field_photo_layout' => 'Оформление на снимките',
    'field_photo_sorting_col' => 'Колона за сортиране на снимки',
    'field_photo_sorting_order' => 'Ред на сортиране на снимки',
    'field_album_sorting_col' => 'Колона за сортиране на албуми',
    'field_album_sorting_order' => 'Ред на сортиране на албуми',
    'field_album_thumb_aspect_ratio' => 'Съотношение на миниатюрите',
    'field_album_timeline' => 'Хронология на албуми',
    'field_photo_timeline' => 'Хронология на снимки',
    'field_published_at' => 'Дата на публикуване във Flow',
    'field_is_nsfw' => 'Чувствителен',
    'field_is_public' => 'Публичен',
    'field_is_link_required' => 'Изисква връзка',
    'field_grants_full_photo_access' => 'Пълен достъп до снимките',
    'field_grants_download' => 'Изтегляне',
    'field_grants_upload' => 'Качване (SE)',
    'apply' => 'Приложи',
    'cancel' => 'Отказ',

    // Set Owner modal
    'set_owner_title' => 'Задаване на собственик',
    'set_owner_description' => 'Всички избрани албуми ще бъдат преместени на основно ниво, а техните наследници също ще бъдат прехвърлени.',
    'set_owner_select_user' => 'Изберете нов собственик',
    'transfer' => 'Прехвърляне',

    // Delete confirmation modal
    'delete_title' => 'Изтриване на албуми',
    'delete_confirm' => 'На път сте да изтриете окончателно :count албум и всички негови подалбуми и снимки. Това действие не може да бъде отменено.|На път сте да изтриете окончателно :count албума и всички техни подалбуми и снимки. Това действие не може да бъде отменено.',
    'confirm_delete' => 'Потвърждаване на изтриването',

    // Toasts
    'success_patch' => 'Албумите са обновени успешно.',
    'success_set_owner' => 'Собствеността е прехвърлена успешно.',
    'success_delete' => 'Албумите са изтрити успешно.',
    'error_load' => 'Неуспешно зареждане на албумите.',
    'error_load_ids' => 'Неуспешно зареждане на ID на албумите.',
    'error_patch' => 'Неуспешно обновяване на албумите.',
    'error_missing_timezone' => 'Моля, изберете часова зона за датата на публикуване.',
    'error_set_owner' => 'Неуспешно прехвърляне на собствеността.',
    'error_delete' => 'Неуспешно изтриване на албумите.',
    'error_load_users' => 'Неуспешно зареждане на потребителите.',
];
