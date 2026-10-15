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

    'title' => 'Massredigering av album',
    'description' => 'Redigera metadata och synlighetsinställningar för flera album samtidigt.',
    'warning' => 'Ändringar som görs här tillämpas direkt och kan inte ångras. Taggalbum visas inte.',

    // Table columns
    'col_title' => 'Titel',
    'col_owner' => 'Ägare',
    'col_license' => 'Licens',
    'col_is_nsfw' => 'Känsligt',
    'col_is_public' => 'Offentligt',
    'col_is_link_required' => 'Länk',
    'col_grants_full_photo_access' => 'Fullt foto',
    'col_grants_download' => 'Nedladdning',
    'col_grants_upload' => 'Uppladdning',
    'col_photo_sorting' => 'Fotosortering',
    'col_album_sorting' => 'Albumsortering',
    'col_created_at' => 'Skapat',

    // Filter
    'filter_placeholder' => 'Sök efter titel...',

    // Pagination
    'per_page' => 'Per sida',
    'total_selected' => ':n album valt|:n album valda',
    'select_all_page' => 'Välj alla på den här sidan',
    'select_all_matching' => 'Välj alla matchande',
    'cap_warning' => 'Endast de första 1 000 albumen har valts.',

    // Mode toggle
    'mode_paginated' => 'Sidindelad',
    'mode_infinite' => 'Oändlig rullning',

    // Action buttons
    'action_delete' => 'Radera',
    'action_set_owner' => 'Ange ägare',
    'action_edit_fields' => 'Redigera fält',

    // Edit Fields modal
    'edit_fields_title' => 'Redigera fält',
    'edit_fields_description' => 'Endast markerade fält uppdateras. Tomma värden rensar fältet.',
    'section_metadata' => 'Metadata',
    'section_visibility' => 'Synlighet',
    'field_description' => 'Beskrivning',
    'field_copyright' => 'Upphovsrätt',
    'field_license' => 'Licens',
    'field_photo_layout' => 'Fotolayout',
    'field_photo_sorting_col' => 'Sorteringskolumn för foton',
    'field_photo_sorting_order' => 'Sorteringsordning för foton',
    'field_album_sorting_col' => 'Sorteringskolumn för album',
    'field_album_sorting_order' => 'Sorteringsordning för album',
    'field_album_thumb_aspect_ratio' => 'Bildförhållande för miniatyrer',
    'field_album_timeline' => 'Albumtidslinje',
    'field_photo_timeline' => 'Fototidslinje',
    'field_published_at' => 'Publiceringsdatum i Flow',
    'field_is_nsfw' => 'Känsligt',
    'field_is_public' => 'Offentligt',
    'field_is_link_required' => 'Länk krävs',
    'field_grants_full_photo_access' => 'Full åtkomst till foton',
    'field_grants_download' => 'Nedladdning',
    'field_grants_upload' => 'Uppladdning (SE)',
    'apply' => 'Tillämpa',
    'cancel' => 'Avbryt',

    // Set Owner modal
    'set_owner_title' => 'Ange ägare',
    'set_owner_description' => 'Alla valda album flyttas till rotnivån och deras underliggande album överförs också.',
    'set_owner_select_user' => 'Välj ny ägare',
    'transfer' => 'Överför',

    // Delete confirmation modal
    'delete_title' => 'Radera album',
    'delete_confirm' => 'Du är på väg att permanent radera :count album och alla dess underalbum och foton. Åtgärden kan inte ångras.|Du är på väg att permanent radera :count album och alla deras underalbum och foton. Åtgärden kan inte ångras.',
    'confirm_delete' => 'Bekräfta radering',

    // Toasts
    'success_patch' => 'Albumen har uppdaterats.',
    'success_set_owner' => 'Ägarskapet har överförts.',
    'success_delete' => 'Albumen har raderats.',
    'error_load' => 'Det gick inte att läsa in album.',
    'error_load_ids' => 'Det gick inte att läsa in albumens ID.',
    'error_patch' => 'Det gick inte att uppdatera albumen.',
    'error_missing_timezone' => 'Välj en tidszon för publiceringsdatumet.',
    'error_set_owner' => 'Det gick inte att överföra ägarskapet.',
    'error_delete' => 'Det gick inte att radera albumen.',
    'error_load_users' => 'Det gick inte att läsa in användare.',
];
