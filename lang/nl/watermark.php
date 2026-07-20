<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Watermerkvoorbeeld',
        'se_required' => 'De watermerkmodule vereist dat Lychee Supporter Edition (SE) of SE Preview is ingeschakeld.',

        'section_settings' => 'Watermerkinstellingen',
        'section_preview' => 'Live voorbeeld',
        'disclaimer' => 'Dit voorbeeld geeft een indruk van hoe het watermerk eruit zal zien. Het uiteindelijke resultaat op uw echte foto\'s kan licht afwijken.',

        'watermark_photo_id' => 'Foto-ID watermerk',
        'watermark_photo_id_placeholder' => 'Foto-ID van 24 tekens',
        'watermark_photo_id_hint' => 'Foto-ID van de afbeelding die als watermerk wordt gebruikt. Open een foto en kopieer de laatste 24 tekens van de URL.',

        'preview_photo_id' => 'Foto-ID achtergrond',
        'preview_photo_id_placeholder' => 'Foto-ID van 24 tekens',
        'preview_photo_id_hint' => 'Voer een foto-ID in om als achtergrond voor het voorbeeld te gebruiken.',

        'size' => 'Grootte (:value%)',
        'opacity' => 'Dekking (:value%)',
        'position' => 'Positie',
        'position_options' => [
            'top-left' => 'Linksboven',
            'top' => 'Boven midden',
            'top-right' => 'Rechtsboven',
            'left' => 'Midden links',
            'center' => 'Midden',
            'right' => 'Midden rechts',
            'bottom-left' => 'Linksonder',
            'bottom' => 'Onder midden',
            'bottom-right' => 'Rechtsonder',
        ],

        'section_shift' => 'Verschuiving / offset',
        'shift_type' => 'Eenheid verschuiving',
        'shift_type_options' => [
            'relative' => 'Relatief (%)',
            'absolute' => 'Absoluut (px)',
        ],
        'shift_type_hint' => 'Relatieve verschuivingen zijn een percentage van de afbeeldingsgrootte; absolute verschuivingen zijn een vast aantal pixels.',
        'shift_mode_use_slider' => 'Schuifregelaar gebruiken',
        'shift_mode_use_classic' => 'Numeriek invoerveld gebruiken',
        'shift_x' => 'Horizontale verschuiving (:value)',
        'shift_x_direction_options' => [
            'left' => 'Links',
            'right' => 'Rechts',
        ],
        'shift_y' => 'Verticale verschuiving (:value)',
        'shift_y_direction_options' => [
            'up' => 'Omhoog',
            'down' => 'Omlaag',
        ],

        'save' => 'Instellingen opslaan',
        'saved' => 'Watermerkinstellingen opgeslagen.',
        'save_error' => 'Opslaan van watermerkinstellingen is mislukt.',
        'save_requires_se' => 'Het opslaan van watermerkinstellingen vereist een volledige Supporter Edition (SE)-licentie. Met SE Preview kunt u alleen het effect bekijken.',

        'no_watermark_image' => 'Geen watermerkafbeelding geconfigureerd. Voer een foto-ID voor het watermerk in en klik op "Laden" om een voorbeeld te bekijken.',
        'no_preview_photo' => 'Voer hierboven een foto-ID voor de achtergrond in om een voorbeeld van de watermerkoverlay te bekijken.',
        'photo_load_error' => 'De foto kon niet worden geladen. Controleer of het ID juist is en of u er toegang toe heeft.',
        'watermark_load_error' => 'De watermerkafbeelding kon niet worden geladen. Controleer of het foto-ID juist is.',
    ],
];
