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

    'title' => 'Albumok tömeges szerkesztése',
    'description' => 'Több album metaadatainak és láthatósági beállításainak szerkesztése egyszerre.',
    'warning' => 'Az itt végzett módosítások azonnal érvénybe lépnek, és nem vonhatók vissza. A címkealbumok nem jelennek meg.',

    // Table columns
    'col_title' => 'Cím',
    'col_owner' => 'Tulajdonos',
    'col_license' => 'Licenc',
    'col_is_nsfw' => 'Érzékeny',
    'col_is_public' => 'Nyilvános',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Teljes fénykép',
    'col_grants_download' => 'Letöltés',
    'col_grants_upload' => 'Feltöltés',
    'col_photo_sorting' => 'Fényképrendezés',
    'col_album_sorting' => 'Albumrendezés',
    'col_created_at' => 'Létrehozva',
    'taken_range' => 'Készült: :range',
    'taken_none' => 'Nincs adat',

    // Filter
    'filter_placeholder' => 'Keresés cím alapján...',

    // Pagination
    'per_page' => 'Oldalanként',
    'total_selected' => ':n album kiválasztva|:n album kiválasztva',
    'select_all_page' => 'Az oldalon lévők kijelölése',
    'select_all_matching' => 'Összes egyező kijelölése',
    'cap_warning' => 'Csak az első 1000 album lett kijelölve.',

    // Mode toggle
    'mode_paginated' => 'Lapozott',
    'mode_infinite' => 'Végtelen görgetés',

    // Action buttons
    'action_delete' => 'Törlés',
    'action_set_owner' => 'Tulajdonos beállítása',
    'action_edit_fields' => 'Mezők szerkesztése',

    // Edit Fields modal
    'edit_fields_title' => 'Mezők szerkesztése',
    'edit_fields_description' => 'Csak a bejelölt mezők frissülnek. Az üres értékek törlik a mezőt.',
    'section_metadata' => 'Metaadatok',
    'section_visibility' => 'Láthatóság',
    'field_description' => 'Leírás',
    'field_copyright' => 'Szerzői jog',
    'field_license' => 'Licenc',
    'field_photo_layout' => 'Fényképelrendezés',
    'field_photo_sorting_col' => 'Fényképrendezési oszlop',
    'field_photo_sorting_order' => 'Fényképrendezési sorrend',
    'field_album_sorting_col' => 'Albumrendezési oszlop',
    'field_album_sorting_order' => 'Albumrendezési sorrend',
    'field_album_thumb_aspect_ratio' => 'Bélyegkép képaránya',
    'field_album_timeline' => 'Album idővonal',
    'field_photo_timeline' => 'Fénykép idővonal',
    'field_published_at' => 'Flow közzétételi dátum',
    'field_is_nsfw' => 'Érzékeny',
    'field_is_public' => 'Nyilvános',
    'field_is_link_required' => 'Link szükséges',
    'field_grants_full_photo_access' => 'Teljes fényképhozzáférés',
    'field_grants_download' => 'Letöltés',
    'field_grants_upload' => 'Feltöltés (SE)',
    'apply' => 'Alkalmaz',
    'cancel' => 'Mégse',

    // Set Owner modal
    'set_owner_title' => 'Tulajdonos beállítása',
    'set_owner_description' => 'Az összes kijelölt album a gyökérszintre kerül, és az alárendelt elemeik is átadásra kerülnek.',
    'set_owner_select_user' => 'Új tulajdonos kiválasztása',
    'transfer' => 'Átadás',

    // Delete confirmation modal
    'delete_title' => 'Albumok törlése',
    'delete_confirm' => 'Véglegesen törölni készül :count albumot az összes alalbumával és fényképével együtt. Ez a művelet nem vonható vissza.|Véglegesen törölni készül :count albumot az összes alalbumukkal és fényképükkel együtt. Ez a művelet nem vonható vissza.',
    'confirm_delete' => 'Törlés megerősítése',

    // Toasts
    'success_patch' => 'Az albumok sikeresen frissítve.',
    'success_set_owner' => 'A tulajdonjog sikeresen átadva.',
    'success_delete' => 'Az albumok sikeresen törölve.',
    'error_load' => 'Nem sikerült betölteni az albumokat.',
    'error_load_ids' => 'Nem sikerült betölteni az album azonosítókat.',
    'error_patch' => 'Nem sikerült frissíteni az albumokat.',
    'error_missing_timezone' => 'Válasszon időzónát a közzétételi dátumhoz.',
    'error_set_owner' => 'Nem sikerült átadni a tulajdonjogot.',
    'error_delete' => 'Nem sikerült törölni az albumokat.',
    'error_load_users' => 'Nem sikerült betölteni a felhasználókat.',
];
