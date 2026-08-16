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

    'title' => 'Landing Page',
    'tab_settings' => 'Settings',
    'tab_links' => 'Links',
    'tab_featured' => 'Featured',

    'section_layout' => 'Layout & Structure',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Background (Landscape)',
    'section_background_portrait' => 'Background (Portrait)',
    'section_cta_position' => 'Call-to-Action Position',
    'section_login_position' => 'Login Panel',
    'section_meridian' => 'Meridian Rails',
    'section_content' => 'Content',

    'field_layout' => 'Layout',
    'field_intro_screen_enabled' => 'Intro splash screen',
    'field_hero_text_position' => 'Hero text position',
    'field_hero_text_color' => 'Hero text color',
    'field_hero_text_opacity' => 'Hero text opacity',
    'field_animation_preset' => 'Animation preset',
    'field_backdrop_opacity' => 'Backdrop opacity (:value%)',
    'field_cta_text' => 'text',
    'field_cta_text_placeholder' => 'Leave empty for the layout default',
    'field_about_enabled' => 'About section',
    'field_about_text' => 'About text',

    'field_background_mode' => 'Source',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Photo ID',
        'random' => 'Random public photo',
        'latest_album_cover' => 'Latest album cover',
        'random_from_album' => 'Random photo from album',
    ],
    'field_background_url' => 'Image URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Photo ID',
    'field_background_photo_id_placeholder' => '24-character photo ID',
    'field_background_photo_id_hint' => 'Photo ID of the image to use. Open a photo and copy the last 24 characters from the URL.',
    'field_background_album_id' => 'Album ID',
    'field_background_album_id_placeholder' => '24-character album ID',
    'background_load_error' => 'Could not load photo. Make sure the ID is correct and you have access to it.',
    'background_mode_hint' => [
        'random' => 'A random public photo is picked on every page load.',
        'latest_album_cover' => 'The cover of the most recently published public album is used.',
        'random_from_album' => 'A random photo from this album is picked on every page load. The preview only updates after saving.',
    ],

    'preview_orientation_landscape' => 'Preview landscape background',
    'preview_orientation_portrait' => 'Preview portrait background',

    'field_cta_position' => 'Position',
    'cta_position_options' => [
        'top-left' => 'Top Left',
        'top' => 'Top Center',
        'top-right' => 'Top Right',
        'left' => 'Middle Left',
        'center' => 'Center',
        'right' => 'Middle Right',
        'bottom-left' => 'Bottom Left',
        'bottom' => 'Bottom Center',
        'bottom-right' => 'Bottom Right',
    ],
    'field_cta_shift_type' => 'Shift unit',
    'cta_shift_type_options' => [
        'relative' => 'Relative (%)',
        'absolute' => 'Absolute (px)',
    ],
    'cta_shift_type_hint' => 'Relative shifts are a percentage of the viewport size; absolute shifts are a fixed number of pixels.',
    'field_cta_shift_x' => 'Horizontal Shift (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Left',
        'right' => 'Right',
    ],
    'field_cta_shift_y' => 'Vertical Shift (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Up',
        'down' => 'Down',
    ],
    'reset_to_zero' => 'Reset to 0',

    'field_login_position' => 'Position',
    'login_position_options' => [
        'side' => 'Side',
        'center' => 'Center',
    ],
    'field_login_position_hint' => 'On narrow screens the login panel is always centered below the hero, regardless of this setting. It slides in from the reading-direction edge when the animation preset is "Slide reveal" or "Parallax scroll".',

    'field_meridian_explore_offset' => 'Explore label height (:value%)',
    'field_meridian_contact_offset' => 'Contact label height (:value%)',
    'field_meridian_offset_hint' => 'How far down each full-height rail its label sits: 0% is the top, 100% is the bottom.',
    'field_meridian_explore_line_position' => 'Explore rail position (:value%)',
    'field_meridian_contact_line_position' => 'Contact rail position (:value%)',
    'field_meridian_line_position_hint' => 'How far from the left edge each rail sits: 0% is the left edge, 100% is the right edge. The explore rail always centers itself when the contact rail is off.',

    'preview_title' => 'Live Preview',
    'preview_hint' => 'Updates instantly as you edit — nothing is saved until you click Save.',
    'flat_list_hint' => 'These settings are also editable from the flat generic Settings list.',

    'save' => 'Save',
    'saved' => 'Landing page settings saved.',
    'save_error' => 'Failed to save landing page settings.',

    'se_required' => 'This layout/preset requires Lychee SE.',
];
