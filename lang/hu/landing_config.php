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

    'title' => 'Kezdőlap',
    'tab_settings' => 'Beállítások',
    'tab_links' => 'Hivatkozások',
    'tab_featured' => 'Kiemelt',

    'section_layout' => 'Elrendezés és szerkezet',
    'section_hero' => 'Fejléc',
    'section_background_landscape' => 'Háttér (fekvő)',
    'section_background_portrait' => 'Háttér (álló)',
    'section_cta_position' => 'Cselekvésre ösztönző gomb pozíciója',
    'section_login_position' => 'Bejelentkezési panel',
    'section_meridian' => 'Meridian vonalak',
    'section_content' => 'Tartalom',

    'field_layout' => 'Elrendezés',
    'field_intro_screen_enabled' => 'Nyitóképernyő',
    'field_hero_text_position' => 'Fejléc szövegének pozíciója',
    'field_hero_text_color' => 'Fejléc szövegének színe',
    'field_hero_text_opacity' => 'Fejléc szövegének átlátszatlansága',
    'field_animation_preset' => 'Animáció előbeállítása',
    'field_backdrop_opacity' => 'Háttér átlátszatlansága (:value%)',
    'field_cta_text' => 'szöveg',
    'field_cta_text_placeholder' => 'Hagyja üresen az elrendezés alapértelmezéséhez',
    'field_about_enabled' => 'Névjegy szakasz',
    'field_about_text' => 'Névjegy szövege',

    'field_background_mode' => 'Forrás',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Fénykép-azonosító',
        'random' => 'Véletlenszerű nyilvános fénykép',
        'latest_album_cover' => 'Legújabb album borítója',
        'random_from_album' => 'Véletlenszerű fénykép albumból',
    ],
    'field_background_url' => 'Kép URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Fénykép-azonosító',
    'field_background_photo_id_placeholder' => '24 karakteres fénykép-azonosító',
    'field_background_photo_id_hint' => 'A használandó kép fénykép-azonosítója. Nyisson meg egy fényképet, és másolja ki az URL utolsó 24 karakterét.',
    'field_background_album_id' => 'Album-azonosító',
    'field_background_album_id_placeholder' => '24 karakteres album-azonosító',
    'background_load_error' => 'Nem sikerült betölteni a fényképet. Ellenőrizze, hogy az azonosító helyes-e, és van-e hozzáférése.',
    'background_mode_hint' => [
        'random' => 'Minden oldalbetöltéskor egy véletlenszerű nyilvános fénykép kerül kiválasztásra.',
        'latest_album_cover' => 'A legutóbb közzétett nyilvános album borítója kerül felhasználásra.',
        'random_from_album' => 'Minden oldalbetöltéskor egy véletlenszerű fénykép kerül kiválasztásra ebből az albumból. Az előnézet csak mentés után frissül.',
    ],

    'preview_orientation_landscape' => 'Fekvő háttér előnézete',
    'preview_orientation_portrait' => 'Álló háttér előnézete',

    'field_cta_position' => 'Pozíció',
    'cta_position_options' => [
        'top-left' => 'Bal felső',
        'top' => 'Felül középen',
        'top-right' => 'Jobb felső',
        'left' => 'Bal középen',
        'center' => 'Középen',
        'right' => 'Jobb középen',
        'bottom-left' => 'Bal alsó',
        'bottom' => 'Alul középen',
        'bottom-right' => 'Jobb alsó',
    ],
    'field_cta_shift_type' => 'Eltolás mértékegysége',
    'cta_shift_type_options' => [
        'relative' => 'Relatív (%)',
        'absolute' => 'Abszolút (px)',
    ],
    'cta_shift_type_hint' => 'A relatív eltolás a nézetablak méretének százaléka, az abszolút eltolás rögzített képpontszám.',
    'field_cta_shift_x' => 'Vízszintes eltolás (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Balra',
        'right' => 'Jobbra',
    ],
    'field_cta_shift_y' => 'Függőleges eltolás (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Fel',
        'down' => 'Le',
    ],
    'reset_to_zero' => 'Visszaállítás 0-ra',

    'field_login_position' => 'Pozíció',
    'login_position_options' => [
        'side' => 'Oldalt',
        'center' => 'Középen',
    ],
    'field_login_position_hint' => 'Keskeny képernyőn a bejelentkezési panel e beállítástól függetlenül mindig a fejléc alatt, középen jelenik meg. Az olvasási irány felőli szélről csúszik be, ha az animáció előbeállítása "Slide reveal" vagy "Parallax scroll".',

    'field_meridian_explore_offset' => 'Felfedezés felirat magassága (:value%)',
    'field_meridian_contact_offset' => 'Kapcsolat felirat magassága (:value%)',
    'field_meridian_offset_hint' => 'Milyen mélyen helyezkedik el a felirat az egyes teljes magasságú vonalakon: 0% a teteje, 100% az alja.',
    'field_meridian_explore_line_position' => 'Felfedezés vonal pozíciója (:value%)',
    'field_meridian_contact_line_position' => 'Kapcsolat vonal pozíciója (:value%)',
    'field_meridian_line_position_hint' => 'Milyen messze helyezkedik el az egyes vonalak a bal szélétől: 0% a bal szél, 100% a jobb szél. A felfedezés vonal mindig középre igazodik, ha a kapcsolat vonal ki van kapcsolva.',

    'preview_title' => 'Élő előnézet',
    'preview_hint' => 'Szerkesztés közben azonnal frissül — semmi sem kerül mentésre, amíg a Mentés gombra nem kattint.',
    'flat_list_hint' => 'Ezek a beállítások az általános, egyszerű Beállítások listában is szerkeszthetők.',

    'save' => 'Mentés',
    'saved' => 'A kezdőlap beállításai elmentve.',
    'save_error' => 'Nem sikerült menteni a kezdőlap beállításait.',

    'se_required' => 'Ehhez az elrendezéshez/előbeállításhoz Lychee SE szükséges.',
];
