<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Watermark Preview',
        'description' => 'Preview and adjust how watermarks will appear on your photos. Changes are applied with CSS only — no actual image is modified. Save to persist the settings.',
        'se_required' => 'The watermark module requires Lychee Supporter Edition (SE) or SE Preview to be enabled.',

        'section_settings' => 'Watermark Settings',
        'section_preview' => 'Live Preview',
        'section_preview_photo' => 'Preview Photo',

        'watermark_photo_id' => 'Watermark Image ID',
        'watermark_photo_id_placeholder' => '24-character photo ID',
        'watermark_photo_id_hint' => 'Photo ID of the image used as watermark. Open a photo and copy the last 24 characters from the URL.',
        'load_watermark' => 'Load watermark image',

        'preview_photo_id' => 'Background Photo ID',
        'preview_photo_id_placeholder' => '24-character photo ID',
        'preview_photo_id_hint' => 'Enter a photo ID to use as background for the preview.',
        'load_photo' => 'Load photo',

        'size' => 'Size (:value%)',
        'opacity' => 'Opacity (:value%)',
        'position' => 'Position',
        'position_options' => [
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

        'save' => 'Save Settings',
        'saving' => 'Saving…',
        'saved' => 'Watermark settings saved.',
        'save_error' => 'Failed to save watermark settings.',

        'no_watermark_image' => 'No watermark image configured. Enter a watermark photo ID and click "Load" to preview.',
        'no_preview_photo' => 'Enter a background photo ID above to preview the watermark overlay.',
        'photo_load_error' => 'Could not load photo. Make sure the ID is correct and you have access to it.',
        'watermark_load_error' => 'Could not load watermark image. Make sure the photo ID is correct.',

        'placeholder_background' => 'Background photo will appear here',
    ],
];
