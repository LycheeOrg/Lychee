<?php
return [
    /**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */
    'title' => 'Webhooks',
    'description' => 'Konfigurieren Sie ausgehende Webhooks, die ausgelöst werden, wenn Fotos hinzugefügt, verschoben oder gelöscht werden.',
    // Empty state
    'no_webhooks' => 'Es sind noch keine Webhooks konfiguriert.',
    'create_first' => 'Erstellen Sie Ihren ersten Webhook',
    // Table columns
    'col_name' => 'Name',
    'col_event' => 'Veranstaltung',
    'col_method' => 'Methode',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Aktiviert',
    'col_actions' => 'Aktionen',
    // Event labels
    'event_photo_add' => 'Foto hinzugefügt',
    'event_photo_move' => 'Foto verschoben',
    'event_photo_delete' => 'Foto gelöscht',
    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Abfragezeichenfolge',
    // Buttons
    'create' => 'Webhook erstellen',
    'edit' => 'Bearbeiten',
    'delete' => 'Löschen',
    'cancel' => 'Abbrechen',
    'save' => 'Speichern',
    // Form fields
    'field_name' => 'Name',
    'field_name_placeholder' => 'z. B. Mein Webhook',
    'field_event' => 'Veranstaltung',
    'field_method' => 'HTTP-Methode',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Nutzdatenformat',
    'field_enabled' => 'Aktiviert',
    'field_secret' => 'Secret',
    'field_secret_placeholder' => 'Leer lassen, um das bestehende Secret beizubehalten',
    'field_secret_header' => 'Secret Header',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Send Photo ID',
    'field_send_album_id' => 'Send Album ID',
    'field_send_title' => 'Send Title',
    'field_send_size_variants' => 'Send Size Variants',
    // Modal titles
    'modal_create_title' => 'Create Webhook',
    'modal_edit_title' => 'Edit Webhook',
    // Delete confirmation
    'confirm_delete_header' => 'Delete Webhook',
    'confirm_delete_message' => 'Are you sure you want to delete the webhook ":name"? This action cannot be undone.',
    'delete_warning' => 'This action cannot be undone.',
    // Toasts
    'created' => 'Webhook created successfully.',
    'updated' => 'Webhook updated successfully.',
    'deleted' => 'Webhook deleted successfully.',
    'error_load' => 'Failed to load webhooks.',
    'error_save' => 'Failed to save webhook.',
    'error_delete' => 'Failed to delete webhook.',
    // Secret badge
    'has_secret' => 'Secret set',
    'no_secret' => 'No secret',
];
