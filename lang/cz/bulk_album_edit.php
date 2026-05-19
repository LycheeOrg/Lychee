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

    'title' => 'Hromadná úprava alb',
    'description' => 'Upravte metadata a nastavení viditelnosti pro více alb najednou.',
    'warning' => 'Zde provedené změny se uplatní okamžitě a nelze je vrátit zpět. Štítková alba se nezobrazují.',

    // Table columns
    'col_title' => 'Název',
    'col_owner' => 'Vlastník',
    'col_license' => 'Licence',
    'col_is_nsfw' => 'Citlivé',
    'col_is_public' => 'Veřejné',
    'col_is_link_required' => 'Odkaz',
    'col_grants_full_photo_access' => 'Celá fotografie',
    'col_grants_download' => 'Stáhnout',
    'col_grants_upload' => 'Nahrát',
    'col_photo_sorting' => 'Seřadit podle fotek',
    'col_album_sorting' => 'Seřadit podle alb',
    'col_created_at' => 'Vytvořeno',

    // Filter
    'filter_placeholder' => 'Hledat podle názvu...',

    // Pagination
    'per_page' => 'Na stránku',
    'total_selected' => ':n vybrané album|:n vybraných alb',
    'select_all_page' => 'Vybrat vše na této stránce',
    'select_all_matching' => 'Vybrat vše, co odpovídá',
    'cap_warning' => 'Bylo vybráno pouze prvních 1 000 alb.',



    // Mode toggle
    'mode_paginated' => 'Stránkování',
    'mode_infinite' => 'Nekonečné posouvání',

    // Action buttons
    'action_delete' => 'Odstranit',
    'action_set_owner' => 'Nastavit vlastníka',
    'action_edit_fields' => 'Upravit pole',


    // Edit Fields modal
    'edit_fields_title' => 'Upravit',
    'edit_fields_description' => 'Aktualizována budou pouze zaškrtnutá pole. Zadáním prázdné hodnoty pole vymažete.',
    'section_metadata' => 'Metadata',
    'section_visibility' => 'Viditelnost',
    'field_description' => 'Popis',
    'field_copyright' => 'Autorská práva',
    'field_license' => 'Licence',
    'field_photo_layout' => 'Rozložení fotografií',
    'field_photo_sorting_col' => 'Sloupec pro řazení fotografií',
    'field_photo_sorting_order' => 'Pořadí řazení fotografií',
    'field_album_sorting_col' => 'Sloupec pro řazení Alb',
    'field_album_sorting_order' => 'Pořadí řazení Alb',
    'field_album_thumb_aspect_ratio' => 'Poměr stran miniatur',
    'field_album_timeline' => 'Časová osa alba',
    'field_photo_timeline' => 'Časová osa fotografie',
    'field_is_nsfw' => 'Citlivý obsah',
    'field_is_public' => 'Veřejný',
    'field_is_link_required' => 'Vyžaduje odkaz',
    'field_grants_full_photo_access' => 'Plný přístup k fotografiím',
    'field_grants_download' => 'Stahování',
    'field_grants_upload' => 'Nahrát (SE)',
    'apply' => 'Použít',
    'cancel' => 'Zrušit',


    // Set Owner modal
    'set_owner_title' => 'Nastavit vlastníka',
    'set_owner_description' => 'Všechna vybraná alba budou přesunuta do kořenové složky a budou přeneseny i jejich podsložky.',
    'set_owner_select_user' => 'Vybrat nového vlastníka',
    'transfer' => 'Přenést',

    // Delete confirmation modal
    'delete_title' => 'Odstranit alba',
    'delete_confirm' => 'Chystáte se trvale odstranit :count album a všechna jeho podalba i fotografie. Tuto akci nelze vrátit zpět.|Chystáte se trvale odstranit :count alb a všechna jejich podalba i fotografie. Tuto akci nelze vrátit zpět.',
    'confirm_delete' => 'Potvrdit odstranění',


    // Toasts
    'success_patch' => 'Alba byla úspěšně aktualizována.',
    'success_set_owner' => 'Vlastnictví bylo úspěšně převedeno.',
    'success_delete' => 'Alba byla úspěšně smazána.',
    'error_load' => 'Načtení alb se nezdařilo.',
    'error_load_ids' => 'Nepodařilo se načíst ID alb.',
    'error_patch' => 'Nepodařilo se aktualizovat alba.',
    'error_set_owner' => 'Nepodařilo se převést vlastnictví.',
    'error_delete' => 'Nepodařilo se smazat alba.',
    'error_load_users' => 'Nepodařilo se načíst uživatele.',
];
