<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Gallery sizes: preview page and live drawer (Feature 110)
    |--------------------------------------------------------------------------
    */

    'title' => 'Gallery sizes',
    'live_title' => 'Gallery sizes (live)',
    'live_hint' => 'Changes apply to the gallery behind this panel. Closing without saving discards them.',
    'spotlight_action' => 'Adjust gallery sizes (live)',
    'not_allowed' => 'Only administrators can change the gallery sizes.',

    'section_albums' => 'Albums',
    'section_photos' => 'Photos',
    'section_placeholders' => 'Placeholders',

    'album_min_width' => 'Minimum tile width',
    'album_aspect_ratio' => 'Aspect ratio',
    'albums_per_row_mobile' => 'Per row on phones',
    'layout' => 'Layout',
    'layouts' => [
        'justified' => 'Justified',
        'masonry' => 'Masonry',
        'square' => 'Square',
        'grid' => 'Grid',
    ],
    'gap' => 'Gap',
    'justified_row_height' => 'Row height (justified)',
    'masonry_column_width' => 'Column width (masonry)',
    'grid_column_width' => 'Column width (grid)',
    'square_column_width' => 'Column width (square)',
    'px' => ':value px',

    'album_count' => 'Albums',
    'photo_count' => 'Photos',
    'shuffle' => 'Shuffle',
    'album_title' => 'Album :n',

    'width' => 'Width',
    'presets' => [
        'phone' => 'Phone',
        'tablet' => 'Tablet',
        'laptop' => 'Laptop',
        'desktop' => 'Desktop',
        'wide' => 'Wide',
        'window' => 'This window',
    ],
    'shown_at' => ':width px · shown at :scale %',
    'summary' => ':albums albums per row · :rows photo rows',

    'unsaved' => 'Unsaved changes',
    'save' => 'Save',
    'revert' => 'Revert',
    'close' => 'Close',
    'saved' => 'Gallery sizes saved',
    'save_error' => 'Could not save the gallery sizes',
    'load_error' => 'Could not load the settings',
    'reset_hint' => 'Double-click to restore the saved value',
    'live_empty' => 'Nothing on this page uses these sizes.',
];
