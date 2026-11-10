<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Anteprima filigrana',
        'se_required' => 'Il modulo filigrana richiede che Lychee Supporter Edition (SE) o SE Preview sia attivo.',

        'section_settings' => 'Impostazioni filigrana',
        'section_preview' => 'Anteprima dal vivo',
        'disclaimer' => 'Questa anteprima dà un\'idea dell\'aspetto della filigrana. Il risultato finale sulle tue foto potrebbe differire leggermente.',

        'watermark_photo_id' => 'ID immagine filigrana',
        'watermark_photo_id_placeholder' => 'ID foto di 24 caratteri',
        'watermark_photo_id_hint' => 'ID della foto usata come filigrana. Apri una foto e copia gli ultimi 24 caratteri dell\'URL.',

        'preview_photo_id' => 'ID foto di sfondo',
        'preview_photo_id_placeholder' => 'ID foto di 24 caratteri',
        'preview_photo_id_hint' => 'Inserisci un ID foto da usare come sfondo per l\'anteprima.',

        'size' => 'Dimensione (:value%)',
        'opacity' => 'Opacità (:value%)',
        'position' => 'Posizione',
        'position_options' => [
            'top-left' => 'In alto a sinistra',
            'top' => 'In alto al centro',
            'top-right' => 'In alto a destra',
            'left' => 'Al centro a sinistra',
            'center' => 'Centro',
            'right' => 'Al centro a destra',
            'bottom-left' => 'In basso a sinistra',
            'bottom' => 'In basso al centro',
            'bottom-right' => 'In basso a destra',
        ],

        'section_shift' => 'Spostamento / Offset',
        'shift_type' => 'Unità di spostamento',
        'shift_type_options' => [
            'relative' => 'Relativo (%)',
            'absolute' => 'Assoluto (px)',
        ],
        'shift_type_hint' => 'Gli spostamenti relativi sono una percentuale della dimensione dell\'immagine; quelli assoluti sono un numero fisso di pixel.',
        'shift_mode_use_slider' => 'Usa cursore',
        'shift_mode_use_classic' => 'Usa campo numerico',
        'shift_x' => 'Spostamento orizzontale (:value)',
        'shift_x_direction_options' => [
            'left' => 'Sinistra',
            'right' => 'Destra',
        ],
        'shift_y' => 'Spostamento verticale (:value)',
        'shift_y_direction_options' => [
            'up' => 'Su',
            'down' => 'Giù',
        ],
        'reset_to_zero' => 'Reimposta a 0',

        'save' => 'Salva impostazioni',
        'saved' => 'Impostazioni filigrana salvate.',
        'save_error' => 'Impossibile salvare le impostazioni filigrana.',
        'save_requires_se' => 'Il salvataggio delle impostazioni filigrana richiede una licenza Supporter Edition (SE) completa. SE Preview consente solo di visualizzare l\'effetto in anteprima.',

        'no_watermark_image' => 'Nessuna immagine filigrana configurata. Inserisci un ID foto per la filigrana e fai clic su "Carica" per l\'anteprima.',
        'no_preview_photo' => 'Inserisci sopra un ID foto di sfondo per visualizzare in anteprima la filigrana.',
        'photo_load_error' => 'Impossibile caricare la foto. Verifica che l\'ID sia corretto e di avervi accesso.',
        'watermark_load_error' => 'Impossibile caricare l\'immagine filigrana. Verifica che l\'ID foto sia corretto.',
    ],
];
