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

    'title' => 'Webhooky',
    'description' => 'Nakonfigurujte webhooky, které se spustí při přidání, přesunutí nebo odstranění fotografií.',

    // Empty state
    'no_webhooks' => 'Webhooky zatím nejsou nakonfigurovány.',
    'create_first' => 'Vytvořte svůj první webhook',

    // Table columns
    'col_name' => 'Název',
    'col_event' => 'Událost',
    'col_method' => 'Metoda',
    'col_url' => 'URL',
    'col_format' => 'Formát',
    'col_enabled' => 'Povoleno',
    'col_actions' => 'Akce',

    // Event labels
    'event_photo_add' => 'Přidání fotografie',
    'event_photo_move' => 'Přesunutí fotografie',
    'event_photo_delete' => 'Smazání Fotografie',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Dotaz',

    // Buttons
    'create' => 'Vytvořit Webhook',
    'edit' => 'Upravit',
    'delete' => 'Smazat',
    'cancel' => 'Zrušit',
    'save' => 'Uložit',

    // Form fields
    'field_name' => 'Jméno',
    'field_name_placeholder' => 'např. Můj webhook',
    'field_event' => 'Událost',
    'field_method' => 'Metoda HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Formát dat',
    'field_enabled' => 'Povoleno',
    'field_secret' => 'Tajný klíč',
    'field_secret_placeholder' => 'Nechte prázdné, chcete-li zachovat stávající tajný klíč',
    'field_secret_header' => 'Hlavička tajného klíče',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Odeslat ID fotografie',
    'field_send_album_id' => 'Odeslat ID alba',
    'field_send_title' => 'Odeslat Název',
    'field_send_size_variants' => 'Odeslat varianty velikosti',

    // Modal titles
    'modal_create_title' => 'Vytvořit Webhook',
    'modal_edit_title' => 'Upravit Webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Smazat Webhook',
    'confirm_delete_message' => 'Opravdu chcete smazat webhook „:name“? Tuto akci nelze vrátit zpět.',
    'delete_warning' => 'Tuto akci nelze vrátit zpět.',

    // Toasts
    'created' => 'Webhook úspěšně vytvořen.',
    'updated' => 'Webhook úspěšně upraven.',
    'deleted' => 'Webhook úspěšně smazán.',
    'error_load' => 'Chyba při načítání Webhooku.',
    'error_save' => 'Chyba při ukládání Webhooku.',
    'error_delete' => 'Chyba pří mazání Webhooku.',

    // Secret badge
    'has_secret' => 'Tajný klíč nastaven',
    'no_secret' => 'Žádný tajný klíč',
];
