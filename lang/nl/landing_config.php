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

    'title' => 'Landingspagina',
    'tab_settings' => 'Instellingen',
    'tab_links' => 'Links',
    'tab_featured' => 'Uitgelicht',

    'section_layout' => 'Lay-out & structuur',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Achtergrond (liggend)',
    'section_background_portrait' => 'Achtergrond (staand)',
    'section_cta_position' => 'Positie call-to-action',
    'section_login_position' => 'Inlogpaneel',
    'section_meridian' => 'Meridian-lijnen',
    'section_content' => 'Inhoud',

    'field_layout' => 'Lay-out',
    'field_intro_screen_enabled' => 'Introscherm',
    'field_hero_text_position' => 'Positie herotekst',
    'field_hero_text_color' => 'Kleur herotekst',
    'field_hero_text_opacity' => 'Dekking herotekst',
    'field_animation_preset' => 'Animatievoorinstelling',
    'field_backdrop_opacity' => 'Dekking achtergrond (:value%)',
    'field_cta_text' => 'tekst',
    'field_cta_text_placeholder' => 'Leeg laten voor de standaard van de lay-out',
    'field_about_enabled' => 'Over-sectie',
    'field_about_text' => 'Over-tekst',

    'field_background_mode' => 'Bron',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Foto-ID',
        'random' => 'Willekeurige openbare foto',
        'latest_album_cover' => 'Nieuwste albumhoes',
        'random_from_album' => 'Willekeurige foto uit album',
    ],
    'field_background_url' => 'Afbeeldings-URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Foto-ID',
    'field_background_photo_id_placeholder' => 'Foto-ID van 24 tekens',
    'field_background_photo_id_hint' => 'Foto-ID van de te gebruiken afbeelding. Open een foto en kopieer de laatste 24 tekens van de URL.',
    'field_background_album_id' => 'Album-ID',
    'field_background_album_id_placeholder' => 'Album-ID van 24 tekens',
    'background_load_error' => 'Kon de foto niet laden. Controleer of de ID juist is en of u er toegang toe hebt.',
    'background_mode_hint' => [
        'random' => 'Bij elke paginalading wordt een willekeurige openbare foto gekozen.',
        'latest_album_cover' => 'De hoes van het meest recent gepubliceerde openbare album wordt gebruikt.',
        'random_from_album' => 'Bij elke paginalading wordt een willekeurige foto uit dit album gekozen. Het voorbeeld wordt pas bijgewerkt na het opslaan.',
    ],

    'preview_orientation_landscape' => 'Voorbeeld liggende achtergrond',
    'preview_orientation_portrait' => 'Voorbeeld staande achtergrond',

    'field_cta_position' => 'Positie',
    'cta_position_options' => [
        'top-left' => 'Linksboven',
        'top' => 'Midden boven',
        'top-right' => 'Rechtsboven',
        'left' => 'Midden links',
        'center' => 'Midden',
        'right' => 'Midden rechts',
        'bottom-left' => 'Linksonder',
        'bottom' => 'Midden onder',
        'bottom-right' => 'Rechtsonder',
    ],
    'field_cta_shift_type' => 'Verschuivingseenheid',
    'cta_shift_type_options' => [
        'relative' => 'Relatief (%)',
        'absolute' => 'Absoluut (px)',
    ],
    'cta_shift_type_hint' => 'Relatieve verschuivingen zijn een percentage van de viewportgrootte; absolute verschuivingen zijn een vast aantal pixels.',
    'field_cta_shift_x' => 'Horizontale verschuiving (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Links',
        'right' => 'Rechts',
    ],
    'field_cta_shift_y' => 'Verticale verschuiving (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Omhoog',
        'down' => 'Omlaag',
    ],
    'reset_to_zero' => 'Terugzetten naar 0',

    'field_login_position' => 'Positie',
    'login_position_options' => [
        'side' => 'Zijkant',
        'center' => 'Midden',
    ],
    'field_login_position_hint' => 'Op smalle schermen staat het inlogpaneel altijd gecentreerd onder de hero, ongeacht deze instelling. Het schuift in vanaf de rand in leesrichting wanneer de animatievoorinstelling "Slide reveal" of "Parallax scroll" is.',

    'field_meridian_explore_offset' => 'Hoogte label Verkennen (:value%)',
    'field_meridian_contact_offset' => 'Hoogte label Contact (:value%)',
    'field_meridian_offset_hint' => 'Hoe ver omlaag het label op elke lijn over de volle hoogte staat: 0% is bovenaan, 100% is onderaan.',
    'field_meridian_explore_line_position' => 'Positie lijn Verkennen (:value%)',
    'field_meridian_contact_line_position' => 'Positie lijn Contact (:value%)',
    'field_meridian_line_position_hint' => 'Hoe ver van de linkerrand elke lijn staat: 0% is de linkerrand, 100% is de rechterrand. De lijn Verkennen centreert zich altijd wanneer de lijn Contact uit staat.',

    'preview_title' => 'Live voorbeeld',
    'preview_hint' => 'Wordt direct bijgewerkt terwijl u bewerkt — er wordt niets opgeslagen totdat u op Opslaan klikt.',
    'flat_list_hint' => 'Deze instellingen kunnen ook worden bewerkt via de platte algemene lijst met Instellingen.',

    'save' => 'Opslaan',
    'saved' => 'Instellingen van de landingspagina opgeslagen.',
    'save_error' => 'Opslaan van de instellingen van de landingspagina mislukt.',

    'se_required' => 'Deze lay-out/voorinstelling vereist Lychee SE.',
];
