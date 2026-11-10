<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Ukážka vodoznaku',
        'se_required' => 'Modul vodoznaku vyžaduje zapnutú Lychee Supporter Edition (SE) alebo SE Preview.',

        'section_settings' => 'Nastavenia vodoznaku',
        'section_preview' => 'Živá ukážka',
        'disclaimer' => 'Táto ukážka dáva predstavu o tom, ako bude vodoznak vyzerať. Konečný výsledok na vašich fotografiách sa môže mierne líšiť.',

        'watermark_photo_id' => 'ID obrázka vodoznaku',
        'watermark_photo_id_placeholder' => '24-znakové ID fotografie',
        'watermark_photo_id_hint' => 'ID fotografie obrázka použitého ako vodoznak. Otvorte fotografiu a skopírujte posledných 24 znakov z URL.',

        'preview_photo_id' => 'ID fotografie na pozadí',
        'preview_photo_id_placeholder' => '24-znakové ID fotografie',
        'preview_photo_id_hint' => 'Zadajte ID fotografie, ktorá sa použije ako pozadie ukážky.',

        'size' => 'Veľkosť (:value%)',
        'opacity' => 'Nepriehľadnosť (:value%)',
        'position' => 'Poloha',
        'position_options' => [
            'top-left' => 'Vľavo hore',
            'top' => 'Hore v strede',
            'top-right' => 'Vpravo hore',
            'left' => 'Vľavo v strede',
            'center' => 'V strede',
            'right' => 'Vpravo v strede',
            'bottom-left' => 'Vľavo dole',
            'bottom' => 'Dole v strede',
            'bottom-right' => 'Vpravo dole',
        ],

        'section_shift' => 'Posun / odsadenie',
        'shift_type' => 'Jednotka posunu',
        'shift_type_options' => [
            'relative' => 'Relatívny (%)',
            'absolute' => 'Absolútny (px)',
        ],
        'shift_type_hint' => 'Relatívny posun je percentom veľkosti obrázka; absolútny posun je pevný počet pixelov.',
        'shift_mode_use_slider' => 'Použiť posuvník',
        'shift_mode_use_classic' => 'Použiť číselné pole',
        'shift_x' => 'Vodorovný posun (:value)',
        'shift_x_direction_options' => [
            'left' => 'Doľava',
            'right' => 'Doprava',
        ],
        'shift_y' => 'Zvislý posun (:value)',
        'shift_y_direction_options' => [
            'up' => 'Hore',
            'down' => 'Dole',
        ],
        'reset_to_zero' => 'Obnoviť na 0',

        'save' => 'Uložiť nastavenia',
        'saved' => 'Nastavenia vodoznaku boli uložené.',
        'save_error' => 'Nastavenia vodoznaku sa nepodarilo uložiť.',
        'save_requires_se' => 'Uloženie nastavení vodoznaku vyžaduje plnú licenciu Supporter Edition (SE). SE Preview umožňuje iba ukážku efektu.',

        'no_watermark_image' => 'Nie je nastavený žiadny obrázok vodoznaku. Zadajte ID fotografie vodoznaku a kliknite na "Načítať" pre zobrazenie ukážky.',
        'no_preview_photo' => 'Zadajte vyššie ID fotografie na pozadí pre zobrazenie ukážky vodoznaku.',
        'photo_load_error' => 'Fotografiu sa nepodarilo načítať. Skontrolujte, či je ID správne a či k nej máte prístup.',
        'watermark_load_error' => 'Obrázok vodoznaku sa nepodarilo načítať. Skontrolujte, či je ID fotografie správne.',
    ],
];
