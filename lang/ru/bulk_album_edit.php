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

    'title' => 'Массовое редактирование альбомов',
    'description' => 'Редактируйте метаданные и настройки видимости сразу для нескольких альбомов.',
    'warning' => 'Изменения применяются немедленно и не могут быть отменены. Альбомы тегов не отображаются.',

    // Table columns
    'col_title' => 'Название',
    'col_owner' => 'Владелец',
    'col_license' => 'Лицензия',
    'col_is_nsfw' => 'Деликатный',
    'col_is_public' => 'Публичный',
    'col_is_link_required' => 'Ссылка',
    'col_grants_full_photo_access' => 'Оригинал',
    'col_grants_download' => 'Скачивание',
    'col_grants_upload' => 'Загрузка',
    'col_photo_sorting' => 'Сорт. фото',
    'col_album_sorting' => 'Сорт. альбомов',
    'col_created_at' => 'Создан',

    // Filter
    'filter_placeholder' => 'Поиск по названию...',

    // Pagination
    'per_page' => 'На странице',
    'total_selected' => 'Выбрано альбомов: :n|Выбрано альбомов: :n',
    'select_all_page' => 'Выбрать все на этой странице',
    'select_all_matching' => 'Выбрать все подходящие',
    'cap_warning' => 'Выбраны только первые 1000 альбомов.',

    // Mode toggle
    'mode_paginated' => 'Постранично',
    'mode_infinite' => 'Бесконечная прокрутка',

    // Action buttons
    'action_delete' => 'Удалить',
    'action_set_owner' => 'Сменить владельца',
    'action_edit_fields' => 'Изменить поля',

    // Edit Fields modal
    'edit_fields_title' => 'Изменить поля',
    'edit_fields_description' => 'Будут обновлены только отмеченные поля. Пустые значения очищают поле.',
    'section_metadata' => 'Метаданные',
    'section_visibility' => 'Видимость',
    'field_description' => 'Описание',
    'field_copyright' => 'Авторские права',
    'field_license' => 'Лицензия',
    'field_photo_layout' => 'Макет фотографий',
    'field_photo_sorting_col' => 'Столбец сортировки фото',
    'field_photo_sorting_order' => 'Порядок сортировки фото',
    'field_album_sorting_col' => 'Столбец сортировки альбомов',
    'field_album_sorting_order' => 'Порядок сортировки альбомов',
    'field_album_thumb_aspect_ratio' => 'Соотношение сторон миниатюр',
    'field_album_timeline' => 'Хронология альбомов',
    'field_photo_timeline' => 'Хронология фотографий',
    'field_published_at' => 'Дата публикации во Flow',
    'field_is_nsfw' => 'Деликатный',
    'field_is_public' => 'Публичный',
    'field_is_link_required' => 'Требуется ссылка',
    'field_grants_full_photo_access' => 'Доступ к оригиналам',
    'field_grants_download' => 'Скачивание',
    'field_grants_upload' => 'Загрузка (SE)',
    'apply' => 'Применить',
    'cancel' => 'Отмена',

    // Set Owner modal
    'set_owner_title' => 'Сменить владельца',
    'set_owner_description' => 'Все выбранные альбомы будут перемещены на корневой уровень, а их вложенные элементы также будут переданы.',
    'set_owner_select_user' => 'Выберите нового владельца',
    'transfer' => 'Передать',

    // Delete confirmation modal
    'delete_title' => 'Удаление альбомов',
    'delete_confirm' => 'Вы собираетесь безвозвратно удалить :count альбом со всеми его вложенными альбомами и фотографиями. Это действие нельзя отменить.|Вы собираетесь безвозвратно удалить альбомы (:count) со всеми их вложенными альбомами и фотографиями. Это действие нельзя отменить.',
    'confirm_delete' => 'Подтвердить удаление',

    // Toasts
    'success_patch' => 'Альбомы успешно обновлены.',
    'success_set_owner' => 'Права владения успешно переданы.',
    'success_delete' => 'Альбомы успешно удалены.',
    'error_load' => 'Не удалось загрузить альбомы.',
    'error_load_ids' => 'Не удалось загрузить идентификаторы альбомов.',
    'error_patch' => 'Не удалось обновить альбомы.',
    'error_missing_timezone' => 'Выберите часовой пояс для даты публикации.',
    'error_set_owner' => 'Не удалось передать права владения.',
    'error_delete' => 'Не удалось удалить альбомы.',
    'error_load_users' => 'Не удалось загрузить пользователей.',
];
