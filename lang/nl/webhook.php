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
    'description' => 'Configureer uitgaande webhooks die worden geactiveerd wanneer foto\'s worden toegevoegd, verplaatst of verwijderd.',

    // Empty state
    'no_webhooks' => 'Nog geen webhooks geconfigureerd.',
    'create_first' => 'Maak uw eerste webhook aan',

    // Table columns
    'col_name' => 'Naam',
    'col_event' => 'Gebeurtenis',
    'col_method' => 'Methode',
    'col_url' => 'URL',
    'col_format' => 'Formaat',
    'col_enabled' => 'Ingeschakeld',
    'col_actions' => 'Acties',

    // Event labels
    'event_photo_add' => 'Foto toegevoegd',
    'event_photo_move' => 'Foto verplaatst',
    'event_photo_delete' => 'Foto verwijderd',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query-string',

    // Buttons
    'create' => 'Webhook aanmaken',
    'edit' => 'Bewerken',
    'delete' => 'Verwijderen',
    'cancel' => 'Annuleren',
    'save' => 'Opslaan',

    // Form fields
    'field_name' => 'Naam',
    'field_name_placeholder' => 'bijv. Mijn webhook',
    'field_event' => 'Gebeurtenis',
    'field_method' => 'HTTP-methode',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://voorbeeld.nl/hook',
    'field_format' => 'Formaat gegevens',
    'field_enabled' => 'Ingeschakeld',
    'field_secret' => 'Geheim',
    'field_secret_placeholder' => 'Laat leeg om het bestaande geheim te behouden',
    'field_secret_header' => 'Header geheim',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Foto-ID verzenden',
    'field_send_album_id' => 'Album-ID verzenden',
    'field_send_title' => 'Titel verzenden',
    'field_send_size_variants' => 'Formaatvarianten verzenden',

    // Modal titles
    'modal_create_title' => 'Webhook aanmaken',
    'modal_edit_title' => 'Webhook bewerken',

    // Delete confirmation
    'confirm_delete_header' => 'Webhook verwijderen',
    'confirm_delete_message' => 'Weet u zeker dat u de webhook ":name" wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.',
    'delete_warning' => 'Deze actie kan niet ongedaan worden gemaakt.',

    // Toasts
    'created' => 'Webhook succesvol aangemaakt.',
    'updated' => 'Webhook succesvol bijgewerkt.',
    'deleted' => 'Webhook succesvol verwijderd.',
    'error_load' => 'Webhooks laden is mislukt.',
    'error_save' => 'Webhook opslaan is mislukt.',
    'error_delete' => 'Webhook verwijderen is mislukt.',

    // Secret badge
    'has_secret' => 'Geheim ingesteld',
    'no_secret' => 'Geen geheim',
];
