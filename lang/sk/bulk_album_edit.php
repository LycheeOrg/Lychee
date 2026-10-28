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

    'title' => 'Hromadná úprava albumov',
    'description' => 'Upravte metadáta a nastavenia viditeľnosti viacerých albumov naraz.',
    'warning' => 'Zmeny vykonané tu sa použijú okamžite a nedajú sa vrátiť späť. Albumy štítkov sa nezobrazujú.',

    // Table columns
    'col_title' => 'Názov',
    'col_owner' => 'Vlastník',
    'col_license' => 'Licencia',
    'col_is_nsfw' => 'Citlivé',
    'col_is_public' => 'Verejné',
    'col_is_link_required' => 'Odkaz',
    'col_grants_full_photo_access' => 'Plná fotografia',
    'col_grants_download' => 'Sťahovanie',
    'col_grants_upload' => 'Nahrávanie',
    'col_photo_sorting' => 'Triedenie fotografií',
    'col_album_sorting' => 'Triedenie albumov',
    'col_created_at' => 'Vytvorené',
    'taken_range' => 'Odfotené: :range',
    'taken_none' => 'Žiadne údaje',

    // Filter
    'filter_placeholder' => 'Hľadať podľa názvu...',

    // Pagination
    'per_page' => 'Na stránku',
    'total_selected' => ':n vybraný album|:n vybrané albumy|:n vybraných albumov',
    'select_all_page' => 'Vybrať všetko na tejto stránke',
    'select_all_matching' => 'Vybrať všetky zodpovedajúce',
    'cap_warning' => 'Vybraných bolo iba prvých 1 000 albumov.',

    // Mode toggle
    'mode_paginated' => 'Stránkovanie',
    'mode_infinite' => 'Nekonečné posúvanie',

    // Action buttons
    'action_delete' => 'Odstrániť',
    'action_set_owner' => 'Nastaviť vlastníka',
    'action_edit_fields' => 'Upraviť polia',

    // Edit Fields modal
    'edit_fields_title' => 'Upraviť polia',
    'edit_fields_description' => 'Aktualizujú sa iba označené polia. Prázdne hodnoty pole vymažú.',
    'section_metadata' => 'Metadáta',
    'section_visibility' => 'Viditeľnosť',
    'field_description' => 'Popis',
    'field_copyright' => 'Autorské práva',
    'field_license' => 'Licencia',
    'field_photo_layout' => 'Rozloženie fotografií',
    'field_photo_sorting_col' => 'Stĺpec triedenia fotografií',
    'field_photo_sorting_order' => 'Poradie triedenia fotografií',
    'field_album_sorting_col' => 'Stĺpec triedenia albumov',
    'field_album_sorting_order' => 'Poradie triedenia albumov',
    'field_album_thumb_aspect_ratio' => 'Pomer strán miniatúry',
    'field_album_timeline' => 'Časová os albumov',
    'field_photo_timeline' => 'Časová os fotografií',
    'field_published_at' => 'Dátum publikovania vo Flow',
    'field_is_nsfw' => 'Citlivé',
    'field_is_public' => 'Verejné',
    'field_is_link_required' => 'Vyžaduje odkaz',
    'field_grants_full_photo_access' => 'Plný prístup k fotografiám',
    'field_grants_download' => 'Sťahovanie',
    'field_grants_upload' => 'Nahrávanie (SE)',
    'apply' => 'Použiť',
    'cancel' => 'Zrušiť',

    // Set Owner modal
    'set_owner_title' => 'Nastaviť vlastníka',
    'set_owner_description' => 'Všetky vybrané albumy budú presunuté na koreňovú úroveň a prevedené budú aj ich podriadené albumy.',
    'set_owner_select_user' => 'Vyberte nového vlastníka',
    'transfer' => 'Previesť',

    // Delete confirmation modal
    'delete_title' => 'Odstrániť albumy',
    'delete_confirm' => 'Chystáte sa natrvalo odstrániť :count album a všetky jeho podalbumy a fotografie. Túto akciu nie je možné vrátiť späť.|Chystáte sa natrvalo odstrániť :count albumy a všetky ich podalbumy a fotografie. Túto akciu nie je možné vrátiť späť.|Chystáte sa natrvalo odstrániť :count albumov a všetky ich podalbumy a fotografie. Túto akciu nie je možné vrátiť späť.',
    'confirm_delete' => 'Potvrdiť odstránenie',

    // Toasts
    'success_patch' => 'Albumy boli úspešne aktualizované.',
    'success_set_owner' => 'Vlastníctvo bolo úspešne prevedené.',
    'success_delete' => 'Albumy boli úspešne odstránené.',
    'error_load' => 'Albumy sa nepodarilo načítať.',
    'error_load_ids' => 'ID albumov sa nepodarilo načítať.',
    'error_patch' => 'Albumy sa nepodarilo aktualizovať.',
    'error_missing_timezone' => 'Vyberte časové pásmo pre dátum publikovania.',
    'error_set_owner' => 'Vlastníctvo sa nepodarilo previesť.',
    'error_delete' => 'Albumy sa nepodarilo odstrániť.',
    'error_load_users' => 'Používateľov sa nepodarilo načítať.',
];
