<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Förhandsvisning av vattenstämpel',
        'se_required' => 'Vattenstämpelmodulen kräver att Lychee Supporter Edition (SE) eller SE-förhandsvisning är aktiverad.',

        'section_settings' => 'Vattenstämpelinställningar',
        'section_preview' => 'Live-förhandsvisning',
        'disclaimer' => 'Denna förhandsvisning ger en uppfattning om hur vattenstämpeln kommer att se ut. Slutresultatet på dina faktiska foton kan skilja sig något.',

        'watermark_photo_id' => 'Vattenstämpelbildens ID',
        'watermark_photo_id_placeholder' => 'Foto-ID på 24 tecken',
        'watermark_photo_id_hint' => 'Foto-ID för bilden som används som vattenstämpel. Öppna ett foto och kopiera de sista 24 tecknen i URL-adressen.',

        'preview_photo_id' => 'Bakgrundsfotots ID',
        'preview_photo_id_placeholder' => 'Foto-ID på 24 tecken',
        'preview_photo_id_hint' => 'Ange ett foto-ID att använda som bakgrund för förhandsvisningen.',

        'size' => 'Storlek (:value%)',
        'opacity' => 'Opacitet (:value%)',
        'position' => 'Position',
        'position_options' => [
            'top-left' => 'Uppe till vänster',
            'top' => 'Uppe i mitten',
            'top-right' => 'Uppe till höger',
            'left' => 'Mitten till vänster',
            'center' => 'Mitten',
            'right' => 'Mitten till höger',
            'bottom-left' => 'Nere till vänster',
            'bottom' => 'Nere i mitten',
            'bottom-right' => 'Nere till höger',
        ],

        'section_shift' => 'Förskjutning',
        'shift_type' => 'Förskjutningsenhet',
        'shift_type_options' => [
            'relative' => 'Relativ (%)',
            'absolute' => 'Absolut (px)',
        ],
        'shift_type_hint' => 'Relativa förskjutningar är en procentandel av bildens storlek; absoluta förskjutningar är ett fast antal pixlar.',
        'shift_mode_use_slider' => 'Använd reglage',
        'shift_mode_use_classic' => 'Använd sifferinmatning',
        'shift_x' => 'Horisontell förskjutning (:value)',
        'shift_x_direction_options' => [
            'left' => 'Vänster',
            'right' => 'Höger',
        ],
        'shift_y' => 'Vertikal förskjutning (:value)',
        'shift_y_direction_options' => [
            'up' => 'Upp',
            'down' => 'Ner',
        ],
        'reset_to_zero' => 'Återställ till 0',

        'save' => 'Spara inställningar',
        'saved' => 'Vattenstämpelinställningarna har sparats.',
        'save_error' => 'Det gick inte att spara vattenstämpelinställningarna.',
        'save_requires_se' => 'För att spara vattenstämpelinställningar krävs en fullständig Supporter Edition (SE)-licens. SE-förhandsvisning tillåter endast förhandsvisning av effekten.',

        'no_watermark_image' => 'Ingen vattenstämpelbild konfigurerad. Ange ett foto-ID för vattenstämpeln och klicka på "Läs in" för att förhandsvisa.',
        'no_preview_photo' => 'Ange ett bakgrundsfoto-ID ovan för att förhandsvisa vattenstämpeln.',
        'photo_load_error' => 'Kunde inte läsa in fotot. Kontrollera att ID-numret är korrekt och att du har åtkomst till det.',
        'watermark_load_error' => 'Kunde inte läsa in vattenstämpelbilden. Kontrollera att foto-ID-numret är korrekt.',
    ],
];
