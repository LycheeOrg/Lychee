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
    'description' => 'Configura i webhook in uscita attivati quando le foto vengono aggiunte, spostate o eliminate.',

    // Empty state
    'no_webhooks' => 'Nessun webhook configurato.',
    'create_first' => 'Crea il tuo primo webhook',

    // Table columns
    'col_name' => 'Nome',
    'col_event' => 'Evento',
    'col_method' => 'Metodo',
    'col_url' => 'URL',
    'col_format' => 'Formato',
    'col_enabled' => 'Attivo',
    'col_actions' => 'Azioni',

    // Event labels
    'event_photo_add' => 'Foto aggiunta',
    'event_photo_move' => 'Foto spostata',
    'event_photo_delete' => 'Foto eliminata',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query string',

    // Buttons
    'create' => 'Crea webhook',
    'edit' => 'Modifica',
    'delete' => 'Elimina',
    'cancel' => 'Annulla',
    'save' => 'Salva',

    // Form fields
    'field_name' => 'Nome',
    'field_name_placeholder' => 'es. Il mio webhook',
    'field_event' => 'Evento',
    'field_method' => 'Metodo HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Formato del payload',
    'field_enabled' => 'Attivo',
    'field_secret' => 'Segreto',
    'field_secret_placeholder' => 'Lascia vuoto per mantenere il segreto esistente',
    'field_secret_header' => 'Header del segreto',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Invia ID foto',
    'field_send_album_id' => 'Invia ID album',
    'field_send_title' => 'Invia titolo',
    'field_send_size_variants' => 'Invia varianti di dimensione',

    // Modal titles
    'modal_create_title' => 'Crea webhook',
    'modal_edit_title' => 'Modifica webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Elimina webhook',
    'confirm_delete_message' => 'Vuoi davvero eliminare il webhook ":name"? Questa azione non può essere annullata.',
    'delete_warning' => 'Questa azione non può essere annullata.',

    // Toasts
    'created' => 'Webhook creato con successo.',
    'updated' => 'Webhook aggiornato con successo.',
    'deleted' => 'Webhook eliminato con successo.',
    'error_load' => 'Impossibile caricare i webhook.',
    'error_save' => 'Impossibile salvare il webhook.',
    'error_delete' => 'Impossibile eliminare il webhook.',

    // Secret badge
    'has_secret' => 'Segreto impostato',
    'no_secret' => 'Nessun segreto',
];
