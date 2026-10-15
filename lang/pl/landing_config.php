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

    'title' => 'Strona startowa',
    'tab_settings' => 'Ustawienia',
    'tab_links' => 'Linki',
    'tab_featured' => 'Wyróżnione',

    'section_layout' => 'Układ i struktura',
    'section_hero' => 'Sekcja główna (hero)',
    'section_background_landscape' => 'Tło (poziome)',
    'section_background_portrait' => 'Tło (pionowe)',
    'section_cta_position' => 'Położenie przycisku CTA',
    'section_login_position' => 'Panel logowania',
    'section_meridian' => 'Linie układu Meridian',
    'section_content' => 'Treść',

    'field_layout' => 'Układ',
    'field_intro_screen_enabled' => 'Ekran powitalny',
    'field_hero_text_position' => 'Położenie tekstu hero',
    'field_hero_text_color' => 'Kolor tekstu hero',
    'field_hero_text_opacity' => 'Krycie tekstu hero',
    'field_animation_preset' => 'Preset animacji',
    'field_backdrop_opacity' => 'Krycie tła (:value%)',
    'field_cta_text' => 'tekst',
    'field_cta_text_placeholder' => 'Pozostaw puste, aby użyć domyślnego dla układu',
    'field_about_enabled' => 'Sekcja „O nas”',
    'field_about_text' => 'Tekst sekcji „O nas”',

    'field_background_mode' => 'Źródło',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID zdjęcia',
        'random' => 'Losowe publiczne zdjęcie',
        'latest_album_cover' => 'Okładka najnowszego albumu',
        'random_from_album' => 'Losowe zdjęcie z albumu',
    ],
    'field_background_url' => 'URL obrazu',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID zdjęcia',
    'field_background_photo_id_placeholder' => '24-znakowe ID zdjęcia',
    'field_background_photo_id_hint' => 'ID zdjęcia, które ma zostać użyte. Otwórz zdjęcie i skopiuj ostatnie 24 znaki z adresu URL.',
    'field_background_album_id' => 'ID albumu',
    'field_background_album_id_placeholder' => '24-znakowe ID albumu',
    'background_load_error' => 'Nie udało się załadować zdjęcia. Upewnij się, że ID jest poprawne i masz do niego dostęp.',
    'background_mode_hint' => [
        'random' => 'Przy każdym załadowaniu strony wybierane jest losowe publiczne zdjęcie.',
        'latest_album_cover' => 'Używana jest okładka ostatnio opublikowanego publicznego albumu.',
        'random_from_album' => 'Przy każdym załadowaniu strony wybierane jest losowe zdjęcie z tego albumu. Podgląd aktualizuje się dopiero po zapisaniu.',
    ],

    'preview_orientation_landscape' => 'Podgląd tła poziomego',
    'preview_orientation_portrait' => 'Podgląd tła pionowego',

    'field_cta_position' => 'Położenie',
    'cta_position_options' => [
        'top-left' => 'Lewy górny róg',
        'top' => 'Góra, środek',
        'top-right' => 'Prawy górny róg',
        'left' => 'Lewa strona, środek',
        'center' => 'Środek',
        'right' => 'Prawa strona, środek',
        'bottom-left' => 'Lewy dolny róg',
        'bottom' => 'Dół, środek',
        'bottom-right' => 'Prawy dolny róg',
    ],
    'field_cta_shift_type' => 'Jednostka przesunięcia',
    'cta_shift_type_options' => [
        'relative' => 'Względna (%)',
        'absolute' => 'Bezwzględna (px)',
    ],
    'cta_shift_type_hint' => 'Przesunięcia względne są procentem rozmiaru okna; przesunięcia bezwzględne to stała liczba pikseli.',
    'field_cta_shift_x' => 'Przesunięcie poziome (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'W lewo',
        'right' => 'W prawo',
    ],
    'field_cta_shift_y' => 'Przesunięcie pionowe (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'W górę',
        'down' => 'W dół',
    ],
    'reset_to_zero' => 'Zresetuj do 0',

    'field_login_position' => 'Położenie',
    'login_position_options' => [
        'side' => 'Z boku',
        'center' => 'Na środku',
    ],
    'field_login_position_hint' => 'Na wąskich ekranach panel logowania jest zawsze wyśrodkowany pod sekcją hero, niezależnie od tego ustawienia. Wsuwa się od krawędzi zgodnej z kierunkiem czytania, gdy preset animacji to "Slide reveal" lub "Parallax scroll".',

    'field_meridian_explore_offset' => 'Wysokość etykiety „Odkrywaj” (:value%)',
    'field_meridian_contact_offset' => 'Wysokość etykiety „Kontakt” (:value%)',
    'field_meridian_offset_hint' => 'Jak nisko na każdej linii o pełnej wysokości znajduje się jej etykieta: 0% to góra, 100% to dół.',
    'field_meridian_explore_line_position' => 'Położenie linii „Odkrywaj” (:value%)',
    'field_meridian_contact_line_position' => 'Położenie linii „Kontakt” (:value%)',
    'field_meridian_line_position_hint' => 'Jak daleko od lewej krawędzi znajduje się każda linia: 0% to lewa krawędź, 100% to prawa krawędź. Linia „Odkrywaj” zawsze się wyśrodkowuje, gdy linia „Kontakt” jest wyłączona.',

    'preview_title' => 'Podgląd na żywo',
    'preview_hint' => 'Aktualizuje się natychmiast podczas edycji — nic nie zostanie zapisane, dopóki nie klikniesz Zapisz.',
    'flat_list_hint' => 'Te ustawienia można również edytować na ogólnej, płaskiej liście ustawień.',

    'save' => 'Zapisz',
    'saved' => 'Ustawienia strony startowej zostały zapisane.',
    'save_error' => 'Nie udało się zapisać ustawień strony startowej.',

    'se_required' => 'Ten układ/preset wymaga Lychee SE.',
];
