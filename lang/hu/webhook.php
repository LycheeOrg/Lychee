<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Kimenő webhookok beállítása, amelyek fényképek hozzáadásakor, áthelyezésekor vagy törlésekor aktiválódnak.',

    // Empty state
    'no_webhooks' => 'Még nincs beállított webhook.',
    'create_first' => 'Hozza létre első webhookját',

    // Table columns
    'col_name' => 'Név',
    'col_event' => 'Esemény',
    'col_method' => 'Metódus',
    'col_url' => 'URL',
    'col_format' => 'Formátum',
    'col_enabled' => 'Engedélyezve',
    'col_actions' => 'Műveletek',

    // Event labels
    'event_photo_add' => 'Fénykép hozzáadva',
    'event_photo_move' => 'Fénykép áthelyezve',
    'event_photo_delete' => 'Fénykép törölve',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Lekérdezési karakterlánc',

    // Buttons
    'create' => 'Webhook létrehozása',
    'edit' => 'Szerkesztés',
    'delete' => 'Törlés',
    'cancel' => 'Mégse',
    'save' => 'Mentés',

    // Form fields
    'field_name' => 'Név',
    'field_name_placeholder' => 'pl. Saját webhook',
    'field_event' => 'Esemény',
    'field_method' => 'HTTP-metódus',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload formátuma',
    'field_enabled' => 'Engedélyezve',
    'field_secret' => 'Titkos kulcs',
    'field_secret_placeholder' => 'Hagyja üresen a meglévő titkos kulcs megtartásához',
    'field_secret_header' => 'Titkos kulcs fejléce',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Fényképazonosító küldése',
    'field_send_album_id' => 'Albumazonosító küldése',
    'field_send_title' => 'Cím küldése',
    'field_send_size_variants' => 'Méretváltozatok küldése',

    // Modal titles
    'modal_create_title' => 'Webhook létrehozása',
    'modal_edit_title' => 'Webhook szerkesztése',

    // Delete confirmation
    'confirm_delete_header' => 'Webhook törlése',
    'confirm_delete_message' => 'Biztosan törli a(z) ":name" webhookot? Ez a művelet nem vonható vissza.',
    'delete_warning' => 'Ez a művelet nem vonható vissza.',

    // Toasts
    'created' => 'A webhook létrehozása sikeres.',
    'updated' => 'A webhook frissítése sikeres.',
    'deleted' => 'A webhook törlése sikeres.',
    'error_load' => 'Nem sikerült betölteni a webhookokat.',
    'error_save' => 'Nem sikerült menteni a webhookot.',
    'error_delete' => 'Nem sikerült törölni a webhookot.',

    // Secret badge
    'has_secret' => 'Titkos kulcs beállítva',
    'no_secret' => 'Nincs titkos kulcs',
];
