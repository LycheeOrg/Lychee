<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Watermark Preview',
        'se_required' => 'The watermark module requires Lychee Supporter Edition (SE) or SE Preview to be enabled.',

        'section_settings' => 'Watermark Settings',
        'section_preview' => 'Live Preview',
        'disclaimer' => 'This preview gives an idea of how the watermark will look. The final result on your actual photos may differ slightly.',

        'watermark_photo_id' => 'Watermark Image ID',
        'watermark_photo_id_placeholder' => '24-character photo ID',
        'watermark_photo_id_hint' => 'Photo ID of the image used as watermark. Open a photo and copy the last 24 characters from the URL.',

        'preview_photo_id' => 'Background Photo ID',
        'preview_photo_id_placeholder' => '24-character photo ID',
        'preview_photo_id_hint' => 'Enter a photo ID to use as background for the preview.',

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

        'section_shift' => 'Shift / Offset',
        'shift_type' => 'Shift Unit',
        'shift_type_options' => [
            'relative' => 'Relative (%)',
            'absolute' => 'Absolute (px)',
        ],
        'shift_type_hint' => 'Relative shifts are a percentage of the image size; absolute shifts are a fixed number of pixels.',
        'shift_mode_use_slider' => 'Use slider',
        'shift_mode_use_classic' => 'Use number input',
        'shift_x' => 'Horizontal Shift (:value)',
        'shift_x_direction_options' => [
            'left' => 'Left',
            'right' => 'Right',
        ],
        'shift_y' => 'Vertical Shift (:value)',
        'shift_y_direction_options' => [
            'up' => 'Up',
            'down' => 'Down',
        ],

        'save' => 'Save Settings',
        'saved' => 'Watermark settings saved.',
        'save_error' => 'Failed to save watermark settings.',
        'save_requires_se' => 'Saving watermark settings requires a full Supporter Edition (SE) license. SE Preview only allows previewing the effect.',

        'no_watermark_image' => 'No watermark image configured. Enter a watermark photo ID and click "Load" to preview.',
        'no_preview_photo' => 'Enter a background photo ID above to preview the watermark overlay.',
        'photo_load_error' => 'Could not load photo. Make sure the ID is correct and you have access to it.',
        'watermark_load_error' => 'Could not load watermark image. Make sure the photo ID is correct.',
    ],
];
