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

    'title' => 'Начална страница',
    'tab_settings' => 'Настройки',
    'tab_links' => 'Връзки',
    'tab_featured' => 'Препоръчани',

    'section_layout' => 'Оформление и структура',
    'section_hero' => 'Заглавна секция',
    'section_background_landscape' => 'Фон (хоризонтален)',
    'section_background_portrait' => 'Фон (вертикален)',
    'section_cta_position' => 'Позиция на бутона за действие',
    'section_login_position' => 'Панел за вход',
    'section_meridian' => 'Линии на Meridian',
    'section_content' => 'Съдържание',

    'field_layout' => 'Оформление',
    'field_intro_screen_enabled' => 'Встъпителен екран',
    'field_hero_text_position' => 'Позиция на заглавния текст',
    'field_hero_text_color' => 'Цвят на заглавния текст',
    'field_hero_text_opacity' => 'Непрозрачност на заглавния текст',
    'field_animation_preset' => 'Анимация',
    'field_backdrop_opacity' => 'Непрозрачност на фона (:value%)',
    'field_cta_text' => 'текст',
    'field_cta_text_placeholder' => 'Оставете празно за стойността по подразбиране на оформлението',
    'field_about_enabled' => 'Секция „За нас“',
    'field_about_text' => 'Текст „За нас“',

    'field_background_mode' => 'Източник',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID на снимка',
        'random' => 'Случайна публична снимка',
        'latest_album_cover' => 'Корица на най-новия албум',
        'random_from_album' => 'Случайна снимка от албум',
    ],
    'field_background_url' => 'URL на изображението',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID на снимка',
    'field_background_photo_id_placeholder' => '24-символно ID на снимка',
    'field_background_photo_id_hint' => 'ID на снимката, която да се използва. Отворете снимка и копирайте последните 24 символа от URL адреса.',
    'field_background_album_id' => 'ID на албум',
    'field_background_album_id_placeholder' => '24-символно ID на албум',
    'background_load_error' => 'Снимката не може да бъде заредена. Уверете се, че ID е правилно и имате достъп до нея.',
    'background_mode_hint' => [
        'random' => 'При всяко зареждане на страницата се избира случайна публична снимка.',
        'latest_album_cover' => 'Използва се корицата на най-скоро публикувания публичен албум.',
        'random_from_album' => 'При всяко зареждане на страницата се избира случайна снимка от този албум. Прегледът се обновява само след запазване.',
    ],

    'preview_orientation_landscape' => 'Преглед на хоризонталния фон',
    'preview_orientation_portrait' => 'Преглед на вертикалния фон',

    'field_cta_position' => 'Позиция',
    'cta_position_options' => [
        'top-left' => 'Горе вляво',
        'top' => 'Горе в центъра',
        'top-right' => 'Горе вдясно',
        'left' => 'В средата вляво',
        'center' => 'В центъра',
        'right' => 'В средата вдясно',
        'bottom-left' => 'Долу вляво',
        'bottom' => 'Долу в центъра',
        'bottom-right' => 'Долу вдясно',
    ],
    'field_cta_shift_type' => 'Единица за отместване',
    'cta_shift_type_options' => [
        'relative' => 'Относително (%)',
        'absolute' => 'Абсолютно (px)',
    ],
    'cta_shift_type_hint' => 'Относителните отмествания са процент от размера на видимата област; абсолютните са фиксиран брой пиксели.',
    'field_cta_shift_x' => 'Хоризонтално отместване (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Наляво',
        'right' => 'Надясно',
    ],
    'field_cta_shift_y' => 'Вертикално отместване (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Нагоре',
        'down' => 'Надолу',
    ],
    'reset_to_zero' => 'Нулиране',

    'field_login_position' => 'Позиция',
    'login_position_options' => [
        'side' => 'Отстрани',
        'center' => 'В центъра',
    ],
    'field_login_position_hint' => 'На тесни екрани панелът за вход винаги е центриран под заглавната секция, независимо от тази настройка. Той се плъзга от ръба по посоката на четене, когато анимацията е "Slide reveal" или "Parallax scroll".',

    'field_meridian_explore_offset' => 'Височина на етикета „Разгледай“ (:value%)',
    'field_meridian_contact_offset' => 'Височина на етикета „Контакт“ (:value%)',
    'field_meridian_offset_hint' => 'Колко надолу по всяка линия с пълна височина е разположен етикетът ѝ: 0% е горе, 100% е долу.',
    'field_meridian_explore_line_position' => 'Позиция на линията „Разгледай“ (:value%)',
    'field_meridian_contact_line_position' => 'Позиция на линията „Контакт“ (:value%)',
    'field_meridian_line_position_hint' => 'Колко далеч от левия ръб е разположена всяка линия: 0% е левият ръб, 100% е десният ръб. Линията „Разгледай“ винаги се центрира, когато линията „Контакт“ е изключена.',

    'preview_title' => 'Преглед на живо',
    'preview_hint' => 'Обновява се веднага при редактиране — нищо не се запазва, докато не натиснете Запази.',
    'flat_list_hint' => 'Тези настройки могат да се редактират и от общия списък с настройки.',

    'save' => 'Запази',
    'saved' => 'Настройките на началната страница са запазени.',
    'save_error' => 'Неуспешно запазване на настройките на началната страница.',

    'se_required' => 'Това оформление/анимация изисква Lychee SE.',
];
