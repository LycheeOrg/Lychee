<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Úvodná stránka',
    'tab_settings' => 'Nastavenia',
    'tab_links' => 'Odkazy',
    'tab_featured' => 'Odporúčané',

    'section_layout' => 'Rozloženie a štruktúra',
    'section_hero' => 'Hlavička (hero)',
    'section_background_landscape' => 'Pozadie (na šírku)',
    'section_background_portrait' => 'Pozadie (na výšku)',
    'section_cta_position' => 'Pozícia výzvy na akciu',
    'section_login_position' => 'Prihlasovací panel',
    'section_meridian' => 'Čiary Meridian',
    'section_content' => 'Obsah',

    'field_layout' => 'Rozloženie',
    'field_intro_screen_enabled' => 'Úvodná obrazovka',
    'field_hero_text_position' => 'Pozícia textu hlavičky',
    'field_hero_text_color' => 'Farba textu hlavičky',
    'field_hero_text_opacity' => 'Nepriehľadnosť textu hlavičky',
    'field_animation_preset' => 'Predvoľba animácie',
    'field_backdrop_opacity' => 'Nepriehľadnosť pozadia (:value%)',
    'field_cta_text' => 'text',
    'field_cta_text_placeholder' => 'Ponechajte prázdne pre predvolené rozloženie',
    'field_about_enabled' => 'Sekcia O nás',
    'field_about_text' => 'Text O nás',

    'field_background_mode' => 'Zdroj',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID fotografie',
        'random' => 'Náhodná verejná fotografia',
        'latest_album_cover' => 'Obal najnovšieho albumu',
        'random_from_album' => 'Náhodná fotografia z albumu',
    ],
    'field_background_url' => 'URL obrázka',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID fotografie',
    'field_background_photo_id_placeholder' => '24-znakové ID fotografie',
    'field_background_photo_id_hint' => 'ID fotografie obrázka, ktorý sa má použiť. Otvorte fotografiu a skopírujte posledných 24 znakov z URL.',
    'field_background_album_id' => 'ID albumu',
    'field_background_album_id_placeholder' => '24-znakové ID albumu',
    'background_load_error' => 'Fotografiu sa nepodarilo načítať. Uistite sa, že ID je správne a že k nej máte prístup.',
    'background_mode_hint' => [
        'random' => 'Pri každom načítaní stránky sa vyberie náhodná verejná fotografia.',
        'latest_album_cover' => 'Použije sa obal naposledy zverejneného verejného albumu.',
        'random_from_album' => 'Pri každom načítaní stránky sa vyberie náhodná fotografia z tohto albumu. Náhľad sa aktualizuje až po uložení.',
    ],

    'preview_orientation_landscape' => 'Náhľad pozadia na šírku',
    'preview_orientation_portrait' => 'Náhľad pozadia na výšku',

    'field_cta_position' => 'Pozícia',
    'cta_position_options' => [
        'top-left' => 'Vľavo hore',
        'top' => 'Hore v strede',
        'top-right' => 'Vpravo hore',
        'left' => 'Vľavo v strede',
        'center' => 'Stred',
        'right' => 'Vpravo v strede',
        'bottom-left' => 'Vľavo dole',
        'bottom' => 'Dole v strede',
        'bottom-right' => 'Vpravo dole',
    ],
    'field_cta_shift_type' => 'Jednotka posunu',
    'cta_shift_type_options' => [
        'relative' => 'Relatívny (%)',
        'absolute' => 'Absolútny (px)',
    ],
    'cta_shift_type_hint' => 'Relatívne posuny sú percentom veľkosti zobrazovacej plochy; absolútne posuny sú pevný počet pixelov.',
    'field_cta_shift_x' => 'Vodorovný posun (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Doľava',
        'right' => 'Doprava',
    ],
    'field_cta_shift_y' => 'Zvislý posun (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Hore',
        'down' => 'Dole',
    ],
    'reset_to_zero' => 'Obnoviť na 0',

    'field_login_position' => 'Pozícia',
    'login_position_options' => [
        'side' => 'Bok',
        'center' => 'Stred',
    ],
    'field_login_position_hint' => 'Na úzkych obrazovkách je prihlasovací panel vždy vycentrovaný pod hlavičkou, bez ohľadu na toto nastavenie. Ak je predvoľba animácie "Slide reveal" alebo "Parallax scroll", vysunie sa z okraja podľa smeru čítania.',

    'field_meridian_explore_offset' => 'Výška popisu Preskúmať (:value%)',
    'field_meridian_contact_offset' => 'Výška popisu Kontakt (:value%)',
    'field_meridian_offset_hint' => 'Ako nízko na každej čiare po celej výške je umiestnený jej popis: 0 % je hore, 100 % je dole.',
    'field_meridian_explore_line_position' => 'Pozícia čiary Preskúmať (:value%)',
    'field_meridian_contact_line_position' => 'Pozícia čiary Kontakt (:value%)',
    'field_meridian_line_position_hint' => 'Ako ďaleko od ľavého okraja je každá čiara: 0 % je ľavý okraj, 100 % je pravý okraj. Čiara Preskúmať sa vždy vycentruje, keď je čiara Kontakt vypnutá.',

    'preview_title' => 'Živý náhľad',
    'preview_hint' => 'Aktualizuje sa okamžite pri úpravách — nič sa neuloží, kým nekliknete na Uložiť.',
    'flat_list_hint' => 'Tieto nastavenia je možné upraviť aj v plochom všeobecnom zozname Nastavení.',

    'save' => 'Uložiť',
    'saved' => 'Nastavenia úvodnej stránky boli uložené.',
    'save_error' => 'Nepodarilo sa uložiť nastavenia úvodnej stránky.',

    'se_required' => 'Toto rozloženie/predvoľba vyžaduje Lychee SE.',
];
