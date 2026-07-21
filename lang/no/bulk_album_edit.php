<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Massrediger album',
    'description' => 'Rediger metadata og synlighetsinnstillinger for flere album samtidig.',
    'warning' => 'Endringer gjort her trer i kraft umiddelbart og kan ikke angres. Taggalbum vises ikke.',

    // Table columns
    'col_title' => 'Tittel',
    'col_owner' => 'Eier',
    'col_license' => 'Lisens',
    'col_is_nsfw' => 'Følsomt',
    'col_is_public' => 'Offentlig',
    'col_is_link_required' => 'Lenke',
    'col_grants_full_photo_access' => 'Fullt bilde',
    'col_grants_download' => 'Nedlasting',
    'col_grants_upload' => 'Opplasting',
    'col_photo_sorting' => 'Bildesortering',
    'col_album_sorting' => 'Albumsortering',
    'col_created_at' => 'Opprettet',

    // Filter
    'filter_placeholder' => 'Søk etter tittel…',

    // Pagination
    'per_page' => 'Per side',
    'total_selected' => ':n album valgt|:n album valgt',
    'select_all_page' => 'Velg alle på denne siden',
    'select_all_matching' => 'Velg alle som samsvarer',
    'cap_warning' => 'Kun de første 1000 albumene er valgt.',

    // Mode toggle
    'mode_paginated' => 'Sidevis',
    'mode_infinite' => 'Uendelig rulling',

    // Action buttons
    'action_delete' => 'Slett',
    'action_set_owner' => 'Angi eier',
    'action_edit_fields' => 'Rediger felt',

    // Edit Fields modal
    'edit_fields_title' => 'Rediger felt',
    'edit_fields_description' => 'Kun avkryssede felt vil bli oppdatert. Tomme verdier tømmer feltet.',
    'section_metadata' => 'Metadata',
    'section_visibility' => 'Synlighet',
    'field_description' => 'Beskrivelse',
    'field_copyright' => 'Opphavsrett',
    'field_license' => 'Lisens',
    'field_photo_layout' => 'Bildelayout',
    'field_photo_sorting_col' => 'Sorteringskolonne for bilder',
    'field_photo_sorting_order' => 'Sorteringsrekkefølge for bilder',
    'field_album_sorting_col' => 'Sorteringskolonne for album',
    'field_album_sorting_order' => 'Sorteringsrekkefølge for album',
    'field_album_thumb_aspect_ratio' => 'Sideforhold for miniatyrbilde',
    'field_album_timeline' => 'Albumtidslinje',
    'field_photo_timeline' => 'Bildetidslinje',
    'field_is_nsfw' => 'Følsomt',
    'field_is_public' => 'Offentlig',
    'field_is_link_required' => 'Lenke påkrevd',
    'field_grants_full_photo_access' => 'Full bildetilgang',
    'field_grants_download' => 'Nedlasting',
    'field_grants_upload' => 'Opplasting (SE)',
    'apply' => 'Bruk',
    'cancel' => 'Avbryt',

    // Set Owner modal
    'set_owner_title' => 'Angi eier',
    'set_owner_description' => 'Alle valgte album vil bli flyttet til rotnivået, og underalbumene deres vil også bli overført.',
    'set_owner_select_user' => 'Velg ny eier',
    'transfer' => 'Overfør',

    // Delete confirmation modal
    'delete_title' => 'Slett album',
    'delete_confirm' => 'Du er i ferd med å permanent slette :count album og alle dets underalbum og bilder. Denne handlingen kan ikke angres.|Du er i ferd med å permanent slette :count album og alle deres underalbum og bilder. Denne handlingen kan ikke angres.',
    'confirm_delete' => 'Bekreft sletting',

    // Toasts
    'success_patch' => 'Album oppdatert.',
    'success_set_owner' => 'Eierskap overført.',
    'success_delete' => 'Album slettet.',
    'error_load' => 'Kunne ikke laste inn album.',
    'error_load_ids' => 'Kunne ikke laste inn album-ID-er.',
    'error_patch' => 'Kunne ikke oppdatere album.',
    'error_set_owner' => 'Kunne ikke overføre eierskap.',
    'error_delete' => 'Kunne ikke slette album.',
    'error_load_users' => 'Kunne ikke laste inn brukere.',
];
