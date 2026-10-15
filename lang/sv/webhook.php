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
    'description' => 'Konfigurera utgående webhooks som utlöses när foton läggs till, flyttas eller raderas.',

    // Empty state
    'no_webhooks' => 'Inga webhooks har konfigurerats ännu.',
    'create_first' => 'Skapa din första webhook',

    // Table columns
    'col_name' => 'Namn',
    'col_event' => 'Händelse',
    'col_method' => 'Metod',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Aktiverad',
    'col_actions' => 'Åtgärder',

    // Event labels
    'event_photo_add' => 'Foto tillagt',
    'event_photo_move' => 'Foto flyttat',
    'event_photo_delete' => 'Foto raderat',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Frågesträng',

    // Buttons
    'create' => 'Skapa webhook',
    'edit' => 'Redigera',
    'delete' => 'Radera',
    'cancel' => 'Avbryt',
    'save' => 'Spara',

    // Form fields
    'field_name' => 'Namn',
    'field_name_placeholder' => 't.ex. Min webhook',
    'field_event' => 'Händelse',
    'field_method' => 'HTTP-metod',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload-format',
    'field_enabled' => 'Aktiverad',
    'field_secret' => 'Hemlighet',
    'field_secret_placeholder' => 'Lämna tomt för att behålla befintlig hemlighet',
    'field_secret_header' => 'Header för hemlighet',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Skicka foto-ID',
    'field_send_album_id' => 'Skicka album-ID',
    'field_send_title' => 'Skicka titel',
    'field_send_size_variants' => 'Skicka storleksvarianter',

    // Modal titles
    'modal_create_title' => 'Skapa webhook',
    'modal_edit_title' => 'Redigera webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Radera webhook',
    'confirm_delete_message' => 'Är du säker på att du vill radera webhooken ":name"? Denna åtgärd kan inte ångras.',
    'delete_warning' => 'Denna åtgärd kan inte ångras.',

    // Toasts
    'created' => 'Webhooken har skapats.',
    'updated' => 'Webhooken har uppdaterats.',
    'deleted' => 'Webhooken har raderats.',
    'error_load' => 'Det gick inte att läsa in webhooks.',
    'error_save' => 'Det gick inte att spara webhooken.',
    'error_delete' => 'Det gick inte att radera webhooken.',

    // Secret badge
    'has_secret' => 'Hemlighet angiven',
    'no_secret' => 'Ingen hemlighet',
];
