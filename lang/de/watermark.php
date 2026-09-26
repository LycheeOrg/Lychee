<?php
return [
    'preview' => [
        /**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
        'title' => 'Vorschau des Wasserzeichens',
        'se_required' => 'Für das Wasserzeichen-Modul muss die Lychee Supporter Edition (SE) oder die SE-Vorschau aktiviert sein.',
        'section_settings' => 'Einstellungen für Wasserzeichen',
        'section_preview' => 'Live-Vorschau',
        'disclaimer' => 'Diese Vorschau vermittelt einen Eindruck davon, wie das Wasserzeichen aussehen wird. Das Endergebnis auf Ihren tatsächlichen Fotos kann geringfügig davon abweichen.',
        'watermark_photo_id' => 'Wasserzeichen-Bild-ID',
        'watermark_photo_id_placeholder' => 'Ausweis mit Foto und 24 Zeichen',
        'watermark_photo_id_hint' => 'Photo ID of the image used as watermark. Open a photo and copy the last 24 characters from the URL.',
        'preview_photo_id' => 'Hintergrundfoto ID',
        'preview_photo_id_placeholder' => '24 Zeichen Foto ID',
        'preview_photo_id_hint' => 'Geben Sie eine Foto-ID ein, welche als Hintergrund für die Vorschau verwendet werden soll.',
        'size' => 'Größe (:value%)',
        'opacity' => 'Deckkraft (:value%)',
        'position' => 'Position',
        'position_options' => [
            'top-left' => 'Oben links',
            'top' => 'Oben in der Mitte',
            'top-right' => 'Oben rechts',
            'left' => 'Mitte links',
            'center' => 'Mitte',
            'right' => 'Mitte rechts',
            'bottom-left' => 'Unten links',
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
        'reset_to_zero' => 'Reset to 0',
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
