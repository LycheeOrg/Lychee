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
    'description' => 'Konfigurer utgående webhooks som utløses når bilder legges til, flyttes eller slettes.',

    // Empty state
    'no_webhooks' => 'Ingen webhooks konfigurert ennå.',
    'create_first' => 'Opprett din første webhook',

    // Table columns
    'col_name' => 'Navn',
    'col_event' => 'Hendelse',
    'col_method' => 'Metode',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Aktivert',
    'col_actions' => 'Handlinger',

    // Event labels
    'event_photo_add' => 'Bilde lagt til',
    'event_photo_move' => 'Bilde flyttet',
    'event_photo_delete' => 'Bilde slettet',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Spørrestreng',

    // Buttons
    'create' => 'Opprett webhook',
    'edit' => 'Rediger',
    'delete' => 'Slett',
    'cancel' => 'Avbryt',
    'save' => 'Lagre',

    // Form fields
    'field_name' => 'Navn',
    'field_name_placeholder' => 'f.eks. Min webhook',
    'field_event' => 'Hendelse',
    'field_method' => 'HTTP-metode',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Nyttelastformat',
    'field_enabled' => 'Aktivert',
    'field_secret' => 'Hemmelighet',
    'field_secret_placeholder' => 'La stå tom for å beholde eksisterende hemmelighet',
    'field_secret_header' => 'Hemmelighetsheader',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Send bilde-ID',
    'field_send_album_id' => 'Send album-ID',
    'field_send_title' => 'Send tittel',
    'field_send_size_variants' => 'Send størrelsesvarianter',

    // Modal titles
    'modal_create_title' => 'Opprett webhook',
    'modal_edit_title' => 'Rediger webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Slett webhook',
    'confirm_delete_message' => 'Er du sikker på at du vil slette webhooken «:name»? Denne handlingen kan ikke angres.',
    'delete_warning' => 'Denne handlingen kan ikke angres.',

    // Toasts
    'created' => 'Webhook opprettet.',
    'updated' => 'Webhook oppdatert.',
    'deleted' => 'Webhook slettet.',
    'error_load' => 'Kunne ikke laste inn webhooks.',
    'error_save' => 'Kunne ikke lagre webhook.',
    'error_delete' => 'Kunne ikke slette webhook.',

    // Secret badge
    'has_secret' => 'Hemmelighet angitt',
    'no_secret' => 'Ingen hemmelighet',
];
