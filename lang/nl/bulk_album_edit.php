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

    'title' => 'Albums in bulk bewerken',
    'description' => 'Bewerk metadata en zichtbaarheidsinstellingen voor meerdere albums tegelijk.',
    'warning' => 'Wijzigingen die hier worden aangebracht, worden direct toegepast en kunnen niet ongedaan worden gemaakt. Tag-albums worden niet weergegeven.',

    // Table columns
    'col_title' => 'Titel',
    'col_owner' => 'Eigenaar',
    'col_license' => 'Licentie',
    'col_is_nsfw' => 'Gevoelig',
    'col_is_public' => 'Openbaar',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Volledige foto',
    'col_grants_download' => 'Downloaden',
    'col_grants_upload' => 'Uploaden',
    'col_photo_sorting' => 'Fotosortering',
    'col_album_sorting' => 'Albumsortering',
    'col_created_at' => 'Aangemaakt',

    // Filter
    'filter_placeholder' => 'Zoeken op titel...',

    // Pagination
    'per_page' => 'Per pagina',
    'total_selected' => ':n album geselecteerd|:n albums geselecteerd',
    'select_all_page' => 'Alles op deze pagina selecteren',
    'select_all_matching' => 'Alle overeenkomende selecteren',
    'cap_warning' => 'Alleen de eerste 1.000 albums zijn geselecteerd.',

    // Mode toggle
    'mode_paginated' => 'Paginering',
    'mode_infinite' => 'Oneindig scrollen',

    // Action buttons
    'action_delete' => 'Verwijderen',
    'action_set_owner' => 'Eigenaar instellen',
    'action_edit_fields' => 'Velden bewerken',

    // Edit Fields modal
    'edit_fields_title' => 'Velden bewerken',
    'edit_fields_description' => 'Alleen aangevinkte velden worden bijgewerkt. Lege waarden wissen het veld.',
    'section_metadata' => 'Metadata',
    'section_visibility' => 'Zichtbaarheid',
    'field_description' => 'Beschrijving',
    'field_copyright' => 'Copyright',
    'field_license' => 'Licentie',
    'field_photo_layout' => 'Foto-lay-out',
    'field_photo_sorting_col' => 'Sorteerkolom foto\'s',
    'field_photo_sorting_order' => 'Sorteervolgorde foto\'s',
    'field_album_sorting_col' => 'Sorteerkolom albums',
    'field_album_sorting_order' => 'Sorteervolgorde albums',
    'field_album_thumb_aspect_ratio' => 'Beeldverhouding miniatuur',
    'field_album_timeline' => 'Albumtijdlijn',
    'field_photo_timeline' => 'Fototijdlijn',
    'field_is_nsfw' => 'Gevoelig',
    'field_is_public' => 'Openbaar',
    'field_is_link_required' => 'Link vereist',
    'field_grants_full_photo_access' => 'Volledige fototoegang',
    'field_grants_download' => 'Downloaden',
    'field_grants_upload' => 'Uploaden (SE)',
    'apply' => 'Toepassen',
    'cancel' => 'Annuleren',

    // Set Owner modal
    'set_owner_title' => 'Eigenaar instellen',
    'set_owner_description' => 'Alle geselecteerde albums worden naar het hoofdniveau verplaatst en hun onderliggende albums worden eveneens overgedragen.',
    'set_owner_select_user' => 'Nieuwe eigenaar selecteren',
    'transfer' => 'Overdragen',

    // Delete confirmation modal
    'delete_title' => 'Albums verwijderen',
    'delete_confirm' => 'U staat op het punt :count album en alle bijbehorende subalbums en foto\'s permanent te verwijderen. Deze actie kan niet ongedaan worden gemaakt.|U staat op het punt :count albums en alle bijbehorende subalbums en foto\'s permanent te verwijderen. Deze actie kan niet ongedaan worden gemaakt.',
    'confirm_delete' => 'Verwijderen bevestigen',

    // Toasts
    'success_patch' => 'Albums zijn succesvol bijgewerkt.',
    'success_set_owner' => 'Eigendom is succesvol overgedragen.',
    'success_delete' => 'Albums zijn succesvol verwijderd.',
    'error_load' => 'Albums laden is mislukt.',
    'error_load_ids' => 'Album-ID\'s laden is mislukt.',
    'error_patch' => 'Albums bijwerken is mislukt.',
    'error_set_owner' => 'Eigendom overdragen is mislukt.',
    'error_delete' => 'Albums verwijderen is mislukt.',
    'error_load_users' => 'Gebruikers laden is mislukt.',
];
