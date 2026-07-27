<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Forhåndsvisning av vannmerke',
        'se_required' => 'Vannmerkemodulen krever at Lychee Supporter Edition (SE) eller SE Preview er aktivert.',

        'section_settings' => 'Vannmerkeinnstillinger',
        'section_preview' => 'Direkte forhåndsvisning',
        'disclaimer' => 'Denne forhåndsvisningen gir et inntrykk av hvordan vannmerket vil se ut. Det endelige resultatet på dine faktiske bilder kan avvike noe.',

        'watermark_photo_id' => 'Bilde-ID for vannmerke',
        'watermark_photo_id_placeholder' => '24-tegns bilde-ID',
        'watermark_photo_id_hint' => 'Bilde-ID for bildet som brukes som vannmerke. Åpne et bilde og kopier de siste 24 tegnene fra URL-en.',

        'preview_photo_id' => 'Bilde-ID for bakgrunn',
        'preview_photo_id_placeholder' => '24-tegns bilde-ID',
        'preview_photo_id_hint' => 'Skriv inn en bilde-ID som skal brukes som bakgrunn for forhåndsvisningen.',

        'size' => 'Størrelse (:value%)',
        'opacity' => 'Ugjennomsiktighet (:value%)',
        'position' => 'Posisjon',
        'position_options' => [
            'top-left' => 'Øverst til venstre',
            'top' => 'Øverst i midten',
            'top-right' => 'Øverst til høyre',
            'left' => 'Midten til venstre',
            'center' => 'Midtstilt',
            'right' => 'Midten til høyre',
            'bottom-left' => 'Nederst til venstre',
            'bottom' => 'Nederst i midten',
            'bottom-right' => 'Nederst til høyre',
        ],

        'section_shift' => 'Forskyvning',
        'shift_type' => 'Enhet for forskyvning',
        'shift_type_options' => [
            'relative' => 'Relativ (%)',
            'absolute' => 'Absolutt (px)',
        ],
        'shift_type_hint' => 'Relativ forskyvning er en prosentandel av bildestørrelsen; absolutt forskyvning er et fast antall piksler.',
        'shift_mode_use_slider' => 'Bruk glidebryter',
        'shift_mode_use_classic' => 'Bruk numerisk inndata',
        'shift_x' => 'Horisontal forskyvning (:value)',
        'shift_x_direction_options' => [
            'left' => 'Venstre',
            'right' => 'Høyre',
        ],
        'shift_y' => 'Vertikal forskyvning (:value)',
        'shift_y_direction_options' => [
            'up' => 'Opp',
            'down' => 'Ned',
        ],

        'save' => 'Lagre innstillinger',
        'saved' => 'Vannmerkeinnstillinger lagret.',
        'save_error' => 'Kunne ikke lagre vannmerkeinnstillinger.',
        'save_requires_se' => 'Lagring av vannmerkeinnstillinger krever en full Supporter Edition (SE)-lisens. SE Preview tillater kun forhåndsvisning av effekten.',

        'no_watermark_image' => 'Ingen vannmerkebilde er konfigurert. Skriv inn en bilde-ID for vannmerket og klikk «Last inn» for å forhåndsvise.',
        'no_preview_photo' => 'Skriv inn en bilde-ID for bakgrunn ovenfor for å forhåndsvise vannmerkeoverlegget.',
        'photo_load_error' => 'Kunne ikke laste inn bildet. Kontroller at ID-en er riktig og at du har tilgang til det.',
        'watermark_load_error' => 'Kunne ikke laste inn vannmerkebildet. Kontroller at bilde-ID-en er riktig.',
    ],
];
