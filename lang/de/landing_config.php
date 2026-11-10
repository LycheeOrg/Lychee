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

    'title' => 'Startseite',
    'tab_settings' => 'Einstellungen',
    'tab_links' => 'Links',
    'tab_featured' => 'Hervorgehoben',

    'section_layout' => 'Layout & Struktur',
    'section_hero' => 'Hero-Bereich',
    'section_background_landscape' => 'Hintergrund (Querformat)',
    'section_background_portrait' => 'Hintergrund (Hochformat)',
    'section_cta_position' => 'Position des Call-to-Action',
    'section_login_position' => 'Login-Bereich',
    'section_meridian' => 'Meridian-Leisten',
    'section_content' => 'Inhalt',

    'field_layout' => 'Layout',
    'field_intro_screen_enabled' => 'Intro-Bildschirm',
    'field_hero_text_position' => 'Textposition im Hero-Bereich',
    'field_hero_text_color' => 'Textfarbe im Hero-Bereich',
    'field_hero_text_opacity' => 'Textdeckkraft im Hero-Bereich',
    'field_animation_preset' => 'Animations-Voreinstellung',
    'field_backdrop_opacity' => 'Deckkraft der Hintergrundabdunkelung (:value %)',
    'field_cta_text' => 'Text',
    'field_cta_text_placeholder' => 'Leer lassen für den Standard des Layouts',
    'field_about_enabled' => 'Bereich „Über“',
    'field_about_text' => 'Text „Über“',

    'field_background_mode' => 'Quelle',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Foto-ID',
        'random' => 'Zufälliges öffentliches Foto',
        'latest_album_cover' => 'Neuestes Albumcover',
        'random_from_album' => 'Zufälliges Foto aus Album',
    ],
    'field_background_url' => 'Bild-URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Foto-ID',
    'field_background_photo_id_placeholder' => '24-stellige Foto-ID',
    'field_background_photo_id_hint' => 'Foto-ID des zu verwendenden Bildes. Öffnen Sie ein Foto und kopieren Sie die letzten 24 Zeichen aus der URL.',
    'field_background_album_id' => 'Album-ID',
    'field_background_album_id_placeholder' => '24-stellige Album-ID',
    'background_load_error' => 'Foto konnte nicht geladen werden. Stellen Sie sicher, dass die ID korrekt ist und Sie Zugriff darauf haben.',
    'background_mode_hint' => [
        'random' => 'Bei jedem Seitenaufruf wird ein zufälliges öffentliches Foto ausgewählt.',
        'latest_album_cover' => 'Das Cover des zuletzt veröffentlichten öffentlichen Albums wird verwendet.',
        'random_from_album' => 'Bei jedem Seitenaufruf wird ein zufälliges Foto aus diesem Album ausgewählt. Die Vorschau wird erst nach dem Speichern aktualisiert.',
    ],

    'preview_orientation_landscape' => 'Vorschau Hintergrund im Querformat',
    'preview_orientation_portrait' => 'Vorschau Hintergrund im Hochformat',

    'field_cta_position' => 'Position',
    'cta_position_options' => [
        'top-left' => 'Oben links',
        'top' => 'Oben Mitte',
        'top-right' => 'Oben rechts',
        'left' => 'Mitte links',
        'center' => 'Mitte',
        'right' => 'Mitte rechts',
        'bottom-left' => 'Unten links',
        'bottom' => 'Unten Mitte',
        'bottom-right' => 'Unten rechts',
    ],
    'field_cta_shift_type' => 'Einheit der Verschiebung',
    'cta_shift_type_options' => [
        'relative' => 'Relativ (%)',
        'absolute' => 'Absolut (px)',
    ],
    'cta_shift_type_hint' => 'Relative Verschiebungen sind ein Prozentsatz der Viewport-Größe; absolute Verschiebungen sind eine feste Anzahl von Pixeln.',
    'field_cta_shift_x' => 'Horizontale Verschiebung (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Links',
        'right' => 'Rechts',
    ],
    'field_cta_shift_y' => 'Vertikale Verschiebung (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Oben',
        'down' => 'Unten',
    ],
    'reset_to_zero' => 'Auf 0 zurücksetzen',

    'field_login_position' => 'Position',
    'login_position_options' => [
        'side' => 'Seitlich',
        'center' => 'Mitte',
    ],
    'field_login_position_hint' => 'Auf schmalen Bildschirmen wird der Login-Bereich unabhängig von dieser Einstellung immer zentriert unter dem Hero-Bereich angezeigt. Er gleitet vom Rand der Leserichtung herein, wenn die Animations-Voreinstellung "Slide reveal" oder "Parallax scroll" ist.',

    'field_meridian_explore_offset' => 'Höhe der Beschriftung „Entdecken“ (:value %)',
    'field_meridian_contact_offset' => 'Höhe der Beschriftung „Kontakt“ (:value %)',
    'field_meridian_offset_hint' => 'Wie weit unten die Beschriftung auf jeder Leiste in voller Höhe sitzt: 0 % ist oben, 100 % ist unten.',
    'field_meridian_explore_line_position' => 'Position der Leiste „Entdecken“ (:value %)',
    'field_meridian_contact_line_position' => 'Position der Leiste „Kontakt“ (:value %)',
    'field_meridian_line_position_hint' => 'Wie weit jede Leiste vom linken Rand entfernt ist: 0 % ist der linke Rand, 100 % der rechte Rand. Die Leiste „Entdecken“ zentriert sich immer, wenn die Leiste „Kontakt“ deaktiviert ist.',

    'preview_title' => 'Live-Vorschau',
    'preview_hint' => 'Wird beim Bearbeiten sofort aktualisiert – gespeichert wird erst, wenn Sie auf Speichern klicken.',
    'flat_list_hint' => 'Diese Einstellungen können auch in der allgemeinen flachen Einstellungsliste bearbeitet werden.',

    'save' => 'Speichern',
    'saved' => 'Einstellungen der Startseite gespeichert.',
    'save_error' => 'Einstellungen der Startseite konnten nicht gespeichert werden.',

    'se_required' => 'Dieses Layout bzw. diese Voreinstellung erfordert Lychee SE.',
];
