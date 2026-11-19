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

    'title' => 'Úvodní stránka',
    'tab_settings' => 'Nastavení',
    'tab_links' => 'Odkazy',
    'tab_featured' => 'Doporučené',

    'section_layout' => 'Rozvržení a struktura',
    'section_hero' => 'Hero sekce',
    'section_background_landscape' => 'Pozadí (na šířku)',
    'section_background_portrait' => 'Pozadí (na výšku)',
    'section_cta_position' => 'Pozice tlačítka výzvy k akci',
    'section_login_position' => 'Přihlašovací panel',
    'section_meridian' => 'Linie Meridian',
    'section_content' => 'Obsah',

    'field_layout' => 'Rozvržení',
    'field_intro_screen_enabled' => 'Úvodní obrazovka',
    'field_hero_text_position' => 'Pozice textu hero sekce',
    'field_hero_text_color' => 'Barva textu hero sekce',
    'field_hero_text_opacity' => 'Průhlednost textu hero sekce',
    'field_animation_preset' => 'Předvolba animace',
    'field_backdrop_opacity' => 'Krytí pozadí (:value%)',
    'field_cta_text' => 'text',
    'field_cta_text_placeholder' => 'Ponechte prázdné pro výchozí hodnotu rozvržení',
    'field_about_enabled' => 'Sekce O nás',
    'field_about_text' => 'Text sekce O nás',

    'field_background_mode' => 'Zdroj',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID fotografie',
        'random' => 'Náhodná veřejná fotografie',
        'latest_album_cover' => 'Obal nejnovějšího alba',
        'random_from_album' => 'Náhodná fotografie z alba',
    ],
    'field_background_url' => 'URL obrázku',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID fotografie',
    'field_background_photo_id_placeholder' => '24znakové ID fotografie',
    'field_background_photo_id_hint' => 'ID fotografie, která se má použít. Otevřete fotografii a zkopírujte posledních 24 znaků z URL.',
    'field_background_album_id' => 'ID alba',
    'field_background_album_id_placeholder' => '24znakové ID alba',
    'background_load_error' => 'Fotografii nelze načíst. Ujistěte se, že je ID správné a že k ní máte přístup.',
    'background_mode_hint' => [
        'random' => 'Při každém načtení stránky se vybere náhodná veřejná fotografie.',
        'latest_album_cover' => 'Použije se obal naposledy zveřejněného veřejného alba.',
        'random_from_album' => 'Při každém načtení stránky se vybere náhodná fotografie z tohoto alba. Náhled se aktualizuje až po uložení.',
    ],

    'preview_orientation_landscape' => 'Náhled pozadí na šířku',
    'preview_orientation_portrait' => 'Náhled pozadí na výšku',

    'field_cta_position' => 'Pozice',
    'cta_position_options' => [
        'top-left' => 'Vlevo nahoře',
        'top' => 'Nahoře uprostřed',
        'top-right' => 'Vpravo nahoře',
        'left' => 'Vlevo uprostřed',
        'center' => 'Uprostřed',
        'right' => 'Vpravo uprostřed',
        'bottom-left' => 'Vlevo dole',
        'bottom' => 'Dole uprostřed',
        'bottom-right' => 'Vpravo dole',
    ],
    'field_cta_shift_type' => 'Jednotka posunu',
    'cta_shift_type_options' => [
        'relative' => 'Relativní (%)',
        'absolute' => 'Absolutní (px)',
    ],
    'cta_shift_type_hint' => 'Relativní posun je procento velikosti zobrazení; absolutní posun je pevný počet pixelů.',
    'field_cta_shift_x' => 'Vodorovný posun (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Vlevo',
        'right' => 'Vpravo',
    ],
    'field_cta_shift_y' => 'Svislý posun (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Nahoru',
        'down' => 'Dolů',
    ],
    'reset_to_zero' => 'Obnovit na 0',

    'field_login_position' => 'Pozice',
    'login_position_options' => [
        'side' => 'Na straně',
        'center' => 'Uprostřed',
    ],
    'field_login_position_hint' => 'Na úzkých obrazovkách je přihlašovací panel vždy vycentrován pod hero sekcí bez ohledu na toto nastavení. Pokud je předvolba animace "Slide reveal" nebo "Parallax scroll", vysune se z okraje ve směru čtení.',

    'field_meridian_explore_offset' => 'Výška popisku Explore (:value%)',
    'field_meridian_contact_offset' => 'Výška popisku Contact (:value%)',
    'field_meridian_offset_hint' => 'Jak nízko na každé linii přes celou výšku je umístěn její popisek: 0% je nahoře, 100% je dole.',
    'field_meridian_explore_line_position' => 'Pozice linie Explore (:value%)',
    'field_meridian_contact_line_position' => 'Pozice linie Contact (:value%)',
    'field_meridian_line_position_hint' => 'Jak daleko od levého okraje je každá linie umístěna: 0% je levý okraj, 100% je pravý okraj. Linie Explore se vždy vycentruje, když je linie Contact vypnutá.',

    'preview_title' => 'Živý náhled',
    'preview_hint' => 'Aktualizuje se okamžitě během úprav — nic se neuloží, dokud nekliknete na Uložit.',
    'flat_list_hint' => 'Tato nastavení lze upravit také v obecném seznamu Nastavení.',

    'save' => 'Uložit',
    'saved' => 'Nastavení úvodní stránky bylo uloženo.',
    'save_error' => 'Nastavení úvodní stránky se nepodařilo uložit.',

    'se_required' => 'Toto rozvržení/předvolba vyžaduje Lychee SE.',
];
