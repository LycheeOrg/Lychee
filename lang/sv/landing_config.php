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

    'title' => 'Startsida',
    'tab_settings' => 'Inställningar',
    'tab_links' => 'Länkar',
    'tab_featured' => 'Utvalt',

    'section_layout' => 'Layout och struktur',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Bakgrund (liggande)',
    'section_background_portrait' => 'Bakgrund (stående)',
    'section_cta_position' => 'Position för uppmaningsknapp',
    'section_login_position' => 'Inloggningspanel',
    'section_meridian' => 'Meridian-linjer',
    'section_content' => 'Innehåll',

    'field_layout' => 'Layout',
    'field_intro_screen_enabled' => 'Introskärm',
    'field_hero_text_position' => 'Position för hero-text',
    'field_hero_text_color' => 'Färg på hero-text',
    'field_hero_text_opacity' => 'Opacitet för hero-text',
    'field_animation_preset' => 'Animationsförinställning',
    'field_backdrop_opacity' => 'Bakgrundsopacitet (:value%)',
    'field_cta_text' => 'text',
    'field_cta_text_placeholder' => 'Lämna tomt för layoutens standard',
    'field_about_enabled' => 'Om-avsnitt',
    'field_about_text' => 'Om-text',

    'field_background_mode' => 'Källa',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Foto-ID',
        'random' => 'Slumpmässigt offentligt foto',
        'latest_album_cover' => 'Senaste albumomslag',
        'random_from_album' => 'Slumpmässigt foto från album',
    ],
    'field_background_url' => 'Bild-URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Foto-ID',
    'field_background_photo_id_placeholder' => 'Foto-ID med 24 tecken',
    'field_background_photo_id_hint' => 'Foto-ID för bilden som ska användas. Öppna ett foto och kopiera de sista 24 tecknen i webbadressen.',
    'field_background_album_id' => 'Album-ID',
    'field_background_album_id_placeholder' => 'Album-ID med 24 tecken',
    'background_load_error' => 'Det gick inte att läsa in fotot. Kontrollera att angivet ID är korrekt och att du har åtkomst till det.',
    'background_mode_hint' => [
        'random' => 'Ett slumpmässigt offentligt foto väljs vid varje sidladdning.',
        'latest_album_cover' => 'Omslaget till det senast publicerade offentliga albumet används.',
        'random_from_album' => 'Ett slumpmässigt foto från det här albumet väljs vid varje sidladdning. Förhandsvisningen uppdateras först efter att du sparat.',
    ],

    'preview_orientation_landscape' => 'Förhandsvisa liggande bakgrund',
    'preview_orientation_portrait' => 'Förhandsvisa stående bakgrund',

    'field_cta_position' => 'Position',
    'cta_position_options' => [
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
    'field_cta_shift_type' => 'Förskjutningsenhet',
    'cta_shift_type_options' => [
        'relative' => 'Relativ (%)',
        'absolute' => 'Absolut (px)',
    ],
    'cta_shift_type_hint' => 'Relativa förskjutningar anges i procent av visningsytans storlek. Absoluta förskjutningar är ett fast antal pixlar.',
    'field_cta_shift_x' => 'Horisontell förskjutning (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Vänster',
        'right' => 'Höger',
    ],
    'field_cta_shift_y' => 'Vertikal förskjutning (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Upp',
        'down' => 'Ned',
    ],
    'reset_to_zero' => 'Återställ till 0',

    'field_login_position' => 'Position',
    'login_position_options' => [
        'side' => 'Sida',
        'center' => 'Mitten',
    ],
    'field_login_position_hint' => 'På smala skärmar centreras inloggningspanelen alltid under hero-delen, oavsett denna inställning. Den glider in från läsriktningens kant när animationsförinställningen är "Slide reveal" eller "Parallax scroll".',

    'field_meridian_explore_offset' => 'Höjd för utforska-etikett (:value%)',
    'field_meridian_contact_offset' => 'Höjd för kontakt-etikett (:value%)',
    'field_meridian_offset_hint' => 'Hur långt ned längs varje linje i full höjd dess etikett placeras: 0 % är toppen, 100 % är botten.',
    'field_meridian_explore_line_position' => 'Position för utforska-linje (:value%)',
    'field_meridian_contact_line_position' => 'Position för kontakt-linje (:value%)',
    'field_meridian_line_position_hint' => 'Hur långt från vänsterkanten varje linje placeras: 0 % är vänsterkanten, 100 % är högerkanten. Utforska-linjen centreras alltid när kontakt-linjen är avstängd.',

    'preview_title' => 'Live-förhandsvisning',
    'preview_hint' => 'Uppdateras direkt medan du redigerar — inget sparas förrän du klickar på Spara.',
    'flat_list_hint' => 'Dessa inställningar kan även redigeras i den platta allmänna inställningslistan.',

    'save' => 'Spara',
    'saved' => 'Inställningarna för startsidan har sparats.',
    'save_error' => 'Det gick inte att spara inställningarna för startsidan.',

    'se_required' => 'Den här layouten/förinställningen kräver Lychee SE.',
];
