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

    'title' => 'Landingsside',
    'tab_settings' => 'Innstillinger',
    'tab_links' => 'Lenker',
    'tab_featured' => 'Utvalgt',

    'section_layout' => 'Oppsett og struktur',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Bakgrunn (liggende)',
    'section_background_portrait' => 'Bakgrunn (stående)',
    'section_cta_position' => 'Plassering av handlingsknapp',
    'section_login_position' => 'Påloggingspanel',
    'section_meridian' => 'Meridian-linjer',
    'section_content' => 'Innhold',

    'field_layout' => 'Oppsett',
    'field_intro_screen_enabled' => 'Introskjerm',
    'field_hero_text_position' => 'Plassering av hero-tekst',
    'field_hero_text_color' => 'Farge på hero-tekst',
    'field_hero_text_opacity' => 'Gjennomsiktighet for hero-tekst',
    'field_animation_preset' => 'Animasjonsforhåndsinnstilling',
    'field_backdrop_opacity' => 'Bakgrunnsgjennomsiktighet (:value%)',
    'field_cta_text' => 'tekst',
    'field_cta_text_placeholder' => 'La stå tomt for oppsettets standard',
    'field_about_enabled' => 'Om-seksjon',
    'field_about_text' => 'Om-tekst',

    'field_background_mode' => 'Kilde',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'Bilde-ID',
        'random' => 'Tilfeldig offentlig bilde',
        'latest_album_cover' => 'Nyeste albumforside',
        'random_from_album' => 'Tilfeldig bilde fra album',
    ],
    'field_background_url' => 'Bilde-URL',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'Bilde-ID',
    'field_background_photo_id_placeholder' => 'Bilde-ID på 24 tegn',
    'field_background_photo_id_hint' => 'Bilde-ID for bildet som skal brukes. Åpne et bilde og kopier de siste 24 tegnene fra URL-en.',
    'field_background_album_id' => 'Album-ID',
    'field_background_album_id_placeholder' => 'Album-ID på 24 tegn',
    'background_load_error' => 'Kunne ikke laste bildet. Kontroller at ID-en er riktig og at du har tilgang til det.',
    'background_mode_hint' => [
        'random' => 'Et tilfeldig offentlig bilde velges ved hver sideinnlasting.',
        'latest_album_cover' => 'Forsiden til det sist publiserte offentlige albumet brukes.',
        'random_from_album' => 'Et tilfeldig bilde fra dette albumet velges ved hver sideinnlasting. Forhåndsvisningen oppdateres først etter lagring.',
    ],

    'preview_orientation_landscape' => 'Forhåndsvis liggende bakgrunn',
    'preview_orientation_portrait' => 'Forhåndsvis stående bakgrunn',

    'field_cta_position' => 'Plassering',
    'cta_position_options' => [
        'top-left' => 'Øverst til venstre',
        'top' => 'Øverst i midten',
        'top-right' => 'Øverst til høyre',
        'left' => 'Midt til venstre',
        'center' => 'Midten',
        'right' => 'Midt til høyre',
        'bottom-left' => 'Nederst til venstre',
        'bottom' => 'Nederst i midten',
        'bottom-right' => 'Nederst til høyre',
    ],
    'field_cta_shift_type' => 'Forskyvningsenhet',
    'cta_shift_type_options' => [
        'relative' => 'Relativ (%)',
        'absolute' => 'Absolutt (px)',
    ],
    'cta_shift_type_hint' => 'Relative forskyvninger er en prosentandel av visningsområdets størrelse; absolutte forskyvninger er et fast antall piksler.',
    'field_cta_shift_x' => 'Horisontal forskyvning (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Venstre',
        'right' => 'Høyre',
    ],
    'field_cta_shift_y' => 'Vertikal forskyvning (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Opp',
        'down' => 'Ned',
    ],
    'reset_to_zero' => 'Tilbakestill til 0',

    'field_login_position' => 'Plassering',
    'login_position_options' => [
        'side' => 'Ved siden',
        'center' => 'Midten',
    ],
    'field_login_position_hint' => 'På smale skjermer er påloggingspanelet alltid sentrert under hero-seksjonen, uavhengig av denne innstillingen. Det glir inn fra kanten i leseretningen når animasjonsforhåndsinnstillingen er "Slide reveal" eller "Parallax scroll".',

    'field_meridian_explore_offset' => 'Høyde for Utforsk-etikett (:value%)',
    'field_meridian_contact_offset' => 'Høyde for Kontakt-etikett (:value%)',
    'field_meridian_offset_hint' => 'Hvor langt ned på hver fullhøydelinje etiketten står: 0% er toppen, 100% er bunnen.',
    'field_meridian_explore_line_position' => 'Plassering av Utforsk-linjen (:value%)',
    'field_meridian_contact_line_position' => 'Plassering av Kontakt-linjen (:value%)',
    'field_meridian_line_position_hint' => 'Hvor langt fra venstre kant hver linje står: 0% er venstre kant, 100% er høyre kant. Utforsk-linjen sentreres alltid når Kontakt-linjen er av.',

    'preview_title' => 'Direkte forhåndsvisning',
    'preview_hint' => 'Oppdateres umiddelbart mens du redigerer — ingenting lagres før du klikker Lagre.',
    'flat_list_hint' => 'Disse innstillingene kan også redigeres fra den generelle, flate innstillingslisten.',

    'save' => 'Lagre',
    'saved' => 'Innstillingene for landingssiden er lagret.',
    'save_error' => 'Kunne ikke lagre innstillingene for landingssiden.',

    'se_required' => 'Dette oppsettet/denne forhåndsinnstillingen krever Lychee SE.',
];
