<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Главная страница',
    'tab_settings' => 'Настройки',
    'tab_links' => 'Ссылки',
    'tab_featured' => 'Избранное',

    'section_layout' => 'Макет и структура',
    'section_hero' => 'Заглавный блок',
    'section_background_landscape' => 'Фон (альбомная ориентация)',
    'section_background_portrait' => 'Фон (портретная ориентация)',
    'section_cta_position' => 'Положение кнопки призыва к действию',
    'section_login_position' => 'Панель входа',
    'section_meridian' => 'Линии Meridian',
    'section_content' => 'Содержимое',

    'field_layout' => 'Макет',
    'field_intro_screen_enabled' => 'Вступительная заставка',
    'field_hero_text_position' => 'Положение текста заглавного блока',
    'field_hero_text_color' => 'Цвет текста заглавного блока',
    'field_hero_text_opacity' => 'Непрозрачность текста заглавного блока',
    'field_animation_preset' => 'Набор анимации',
    'field_backdrop_opacity' => 'Непрозрачность подложки (:value%)',
    'field_cta_text' => 'текст',
    'field_cta_text_placeholder' => 'Оставьте пустым для значения макета по умолчанию',
    'field_about_enabled' => 'Раздел «О себе»',
    'field_about_text' => 'Текст «О себе»',

    'field_background_mode' => 'Источник',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID фотографии',
        'random' => 'Случайное публичное фото',
        'latest_album_cover' => 'Обложка последнего альбома',
        'random_from_album' => 'Случайное фото из альбома',
    ],
    'field_background_url' => 'URL изображения',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID фотографии',
    'field_background_photo_id_placeholder' => '24-символьный ID фотографии',
    'field_background_photo_id_hint' => 'ID фотографии используемого изображения. Откройте фото и скопируйте последние 24 символа из URL.',
    'field_background_album_id' => 'ID альбома',
    'field_background_album_id_placeholder' => '24-символьный ID альбома',
    'background_load_error' => 'Не удалось загрузить фото. Убедитесь, что ID указан верно и у вас есть к нему доступ.',
    'background_mode_hint' => [
        'random' => 'При каждой загрузке страницы выбирается случайное публичное фото.',
        'latest_album_cover' => 'Используется обложка последнего опубликованного публичного альбома.',
        'random_from_album' => 'При каждой загрузке страницы выбирается случайное фото из этого альбома. Предпросмотр обновляется только после сохранения.',
    ],

    'preview_orientation_landscape' => 'Предпросмотр альбомного фона',
    'preview_orientation_portrait' => 'Предпросмотр портретного фона',

    'field_cta_position' => 'Положение',
    'cta_position_options' => [
        'top-left' => 'Вверху слева',
        'top' => 'Вверху по центру',
        'top-right' => 'Вверху справа',
        'left' => 'Посередине слева',
        'center' => 'По центру',
        'right' => 'Посередине справа',
        'bottom-left' => 'Внизу слева',
        'bottom' => 'Внизу по центру',
        'bottom-right' => 'Внизу справа',
    ],
    'field_cta_shift_type' => 'Единица смещения',
    'cta_shift_type_options' => [
        'relative' => 'Относительная (%)',
        'absolute' => 'Абсолютная (px)',
    ],
    'cta_shift_type_hint' => 'Относительное смещение задаётся в процентах от размера окна просмотра; абсолютное — в фиксированном количестве пикселей.',
    'field_cta_shift_x' => 'Горизонтальное смещение (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Влево',
        'right' => 'Вправо',
    ],
    'field_cta_shift_y' => 'Вертикальное смещение (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Вверх',
        'down' => 'Вниз',
    ],
    'reset_to_zero' => 'Сбросить на 0',

    'field_login_position' => 'Положение',
    'login_position_options' => [
        'side' => 'Сбоку',
        'center' => 'По центру',
    ],
    'field_login_position_hint' => 'На узких экранах панель входа всегда располагается по центру под заглавным блоком, независимо от этой настройки. Она выезжает с края, соответствующего направлению чтения, если выбран набор анимации "Slide reveal" или "Parallax scroll".',

    'field_meridian_explore_offset' => 'Высота подписи «Обзор» (:value%)',
    'field_meridian_contact_offset' => 'Высота подписи «Контакты» (:value%)',
    'field_meridian_offset_hint' => 'Насколько низко на каждой полноразмерной линии расположена её подпись: 0% — вверху, 100% — внизу.',
    'field_meridian_explore_line_position' => 'Положение линии «Обзор» (:value%)',
    'field_meridian_contact_line_position' => 'Положение линии «Контакты» (:value%)',
    'field_meridian_line_position_hint' => 'Расстояние каждой линии от левого края: 0% — левый край, 100% — правый край. Линия «Обзор» всегда центрируется, если линия «Контакты» отключена.',

    'preview_title' => 'Живой предпросмотр',
    'preview_hint' => 'Обновляется мгновенно при редактировании — ничего не сохраняется, пока вы не нажмёте «Сохранить».',
    'flat_list_hint' => 'Эти настройки также можно изменить в общем списке настроек.',

    'save' => 'Сохранить',
    'saved' => 'Настройки главной страницы сохранены.',
    'save_error' => 'Не удалось сохранить настройки главной страницы.',

    'se_required' => 'Этот макет/набор требует Lychee SE.',
];
