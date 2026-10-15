<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    'preview' => [
        'title' => 'Vízjel előnézete',
        'se_required' => 'A vízjel modulhoz a Lychee Supporter Edition (SE) vagy az SE Preview engedélyezése szükséges.',

        'section_settings' => 'Vízjel beállításai',
        'section_preview' => 'Élő előnézet',
        'disclaimer' => 'Ez az előnézet képet ad arról, hogyan fog kinézni a vízjel. A tényleges fényképeken a végeredmény kissé eltérhet.',

        'watermark_photo_id' => 'Vízjelkép azonosítója',
        'watermark_photo_id_placeholder' => '24 karakteres fényképazonosító',
        'watermark_photo_id_hint' => 'A vízjelként használt kép fényképazonosítója. Nyisson meg egy fényképet, és másolja ki az URL utolsó 24 karakterét.',

        'preview_photo_id' => 'Háttérfénykép azonosítója',
        'preview_photo_id_placeholder' => '24 karakteres fényképazonosító',
        'preview_photo_id_hint' => 'Adja meg az előnézet hátteréül szolgáló fénykép azonosítóját.',

        'size' => 'Méret (:value%)',
        'opacity' => 'Átlátszatlanság (:value%)',
        'position' => 'Pozíció',
        'position_options' => [
            'top-left' => 'Bal felső',
            'top' => 'Felső középső',
            'top-right' => 'Jobb felső',
            'left' => 'Bal középső',
            'center' => 'Középen',
            'right' => 'Jobb középső',
            'bottom-left' => 'Bal alsó',
            'bottom' => 'Alsó középső',
            'bottom-right' => 'Jobb alsó',
        ],

        'section_shift' => 'Eltolás',
        'shift_type' => 'Eltolás mértékegysége',
        'shift_type_options' => [
            'relative' => 'Relatív (%)',
            'absolute' => 'Abszolút (px)',
        ],
        'shift_type_hint' => 'A relatív eltolás a képméret százaléka; az abszolút eltolás rögzített számú képpont.',
        'shift_mode_use_slider' => 'Csúszka használata',
        'shift_mode_use_classic' => 'Számbevitel használata',
        'shift_x' => 'Vízszintes eltolás (:value)',
        'shift_x_direction_options' => [
            'left' => 'Balra',
            'right' => 'Jobbra',
        ],
        'shift_y' => 'Függőleges eltolás (:value)',
        'shift_y_direction_options' => [
            'up' => 'Fel',
            'down' => 'Le',
        ],
        'reset_to_zero' => 'Visszaállítás 0-ra',

        'save' => 'Beállítások mentése',
        'saved' => 'A vízjel beállításai mentve.',
        'save_error' => 'Nem sikerült menteni a vízjel beállításait.',
        'save_requires_se' => 'A vízjel beállításainak mentéséhez teljes Supporter Edition (SE) licenc szükséges. Az SE Preview csak a hatás előnézetét teszi lehetővé.',

        'no_watermark_image' => 'Nincs beállítva vízjelkép. Adja meg egy vízjelfénykép azonosítóját, és kattintson a "Betöltés" gombra az előnézethez.',
        'no_preview_photo' => 'Adjon meg fent egy háttérfénykép-azonosítót a vízjel előnézetéhez.',
        'photo_load_error' => 'Nem sikerült betölteni a fényképet. Ellenőrizze, hogy az azonosító helyes-e, és van-e hozzáférése.',
        'watermark_load_error' => 'Nem sikerült betölteni a vízjelképet. Ellenőrizze, hogy a fényképazonosító helyes-e.',
    ],
];
