<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Náhled vodoznaku',
        'se_required' => 'Modul vodoznaku vyžaduje povolenou edici Lychee Supporter Edition (SE) nebo SE Preview.',

        'section_settings' => 'Nastavení vodoznaku',
        'section_preview' => 'Živý náhled',
        'disclaimer' => 'Tento náhled dává představu o tom, jak bude vodoznak vypadat. Konečný výsledek na vašich skutečných fotografiích se může mírně lišit.',

        'watermark_photo_id' => 'ID obrázku vodoznaku',
        'watermark_photo_id_placeholder' => '24místné ID fotografie',
        'watermark_photo_id_hint' => 'ID fotografie obrázku použitého jako vodoznak. Otevřete fotografii a zkopírujte posledních 24 znaků z URL.',

        'preview_photo_id' => 'ID fotografie na pozadí',
        'preview_photo_id_placeholder' => '24místné ID fotografie',
        'preview_photo_id_hint' => 'Zadejte ID fotografie, která se použije jako pozadí náhledu.',

        'size' => 'Velikost (:value%)',
        'opacity' => 'Krytí (:value%)',
        'position' => 'Pozice',
        'position_options' => [
            'top-left' => 'Vlevo nahoře',
            'top' => 'Nahoře uprostřed',
            'top-right' => 'Vpravo nahoře',
            'left' => 'Vlevo uprostřed',
            'center' => 'Uprostřed',
            'right' => 'Vpravo uprostřed',
            'bottom-left' => 'Vlevo dole',
            'bottom' => 'Dole uprostřed',
            'bottom-right' => 'Vpravo dole',
        ],

        'section_shift' => 'Posun / odsazení',
        'shift_type' => 'Jednotka posunu',
        'shift_type_options' => [
            'relative' => 'Relativní (%)',
            'absolute' => 'Absolutní (px)',
        ],
        'shift_type_hint' => 'Relativní posun je procento velikosti obrázku, absolutní posun je pevný počet pixelů.',
        'shift_mode_use_slider' => 'Použít posuvník',
        'shift_mode_use_classic' => 'Použít číselné pole',
        'shift_x' => 'Vodorovný posun (:value)',
        'shift_x_direction_options' => [
            'left' => 'Vlevo',
            'right' => 'Vpravo',
        ],
        'shift_y' => 'Svislý posun (:value)',
        'shift_y_direction_options' => [
            'up' => 'Nahoru',
            'down' => 'Dolů',
        ],
        'reset_to_zero' => 'Obnovit na 0',

        'save' => 'Uložit nastavení',
        'saved' => 'Nastavení vodoznaku uloženo.',
        'save_error' => 'Nastavení vodoznaku se nepodařilo uložit.',
        'save_requires_se' => 'Uložení nastavení vodoznaku vyžaduje plnou licenci Supporter Edition (SE). SE Preview umožňuje pouze náhled efektu.',

        'no_watermark_image' => 'Není nastaven žádný obrázek vodoznaku. Zadejte ID fotografie vodoznaku a klikněte na "Načíst" pro zobrazení náhledu.',
        'no_preview_photo' => 'Zadejte výše ID fotografie na pozadí pro náhled překrytí vodoznakem.',
        'photo_load_error' => 'Fotografii se nepodařilo načíst. Ověřte, že je ID správné a že k ní máte přístup.',
        'watermark_load_error' => 'Obrázek vodoznaku se nepodařilo načíst. Ověřte, že je ID fotografie správné.',
    ],
];
