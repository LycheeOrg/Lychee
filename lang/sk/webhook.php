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
    'description' => 'Nakonfigurujte odchádzajúce webhooky, ktoré sa spustia pri pridaní, presunutí alebo odstránení fotografií.',

    // Empty state
    'no_webhooks' => 'Zatiaľ nie sú nakonfigurované žiadne webhooky.',
    'create_first' => 'Vytvorte svoj prvý webhook',

    // Table columns
    'col_name' => 'Názov',
    'col_event' => 'Udalosť',
    'col_method' => 'Metóda',
    'col_url' => 'URL',
    'col_format' => 'Formát',
    'col_enabled' => 'Zapnuté',
    'col_actions' => 'Akcie',

    // Event labels
    'event_photo_add' => 'Fotografia pridaná',
    'event_photo_move' => 'Fotografia presunutá',
    'event_photo_delete' => 'Fotografia odstránená',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query string',

    // Buttons
    'create' => 'Vytvoriť webhook',
    'edit' => 'Upraviť',
    'delete' => 'Odstrániť',
    'cancel' => 'Zrušiť',
    'save' => 'Uložiť',

    // Form fields
    'field_name' => 'Názov',
    'field_name_placeholder' => 'napr. Môj webhook',
    'field_event' => 'Udalosť',
    'field_method' => 'HTTP metóda',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Formát payloadu',
    'field_enabled' => 'Zapnuté',
    'field_secret' => 'Tajný kľúč',
    'field_secret_placeholder' => 'Ponechajte prázdne pre zachovanie existujúceho tajného kľúča',
    'field_secret_header' => 'Hlavička tajného kľúča',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Odoslať ID fotografie',
    'field_send_album_id' => 'Odoslať ID albumu',
    'field_send_title' => 'Odoslať názov',
    'field_send_size_variants' => 'Odoslať veľkostné varianty',

    // Modal titles
    'modal_create_title' => 'Vytvoriť webhook',
    'modal_edit_title' => 'Upraviť webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Odstrániť webhook',
    'confirm_delete_message' => 'Naozaj chcete odstrániť webhook ":name"? Túto akciu nie je možné vrátiť späť.',
    'delete_warning' => 'Túto akciu nie je možné vrátiť späť.',

    // Toasts
    'created' => 'Webhook bol úspešne vytvorený.',
    'updated' => 'Webhook bol úspešne aktualizovaný.',
    'deleted' => 'Webhook bol úspešne odstránený.',
    'error_load' => 'Webhooky sa nepodarilo načítať.',
    'error_save' => 'Webhook sa nepodarilo uložiť.',
    'error_delete' => 'Webhook sa nepodarilo odstrániť.',

    // Secret badge
    'has_secret' => 'Tajný kľúč nastavený',
    'no_secret' => 'Bez tajného kľúča',
];
