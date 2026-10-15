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
    'field_secret' => 'Geheimnis',
    'field_secret_placeholder' => 'Leer lassen, um das bestehende Secret beizubehalten',
    'field_secret_header' => 'Geheimnis-Header',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Foto-ID senden',
    'field_send_album_id' => 'Album-ID senden',
    'field_send_title' => 'Titel senden',
    'field_send_size_variants' => 'Größenvarianten senden',
    // Modal titles
    'modal_create_title' => 'Webhook erstellen',
    'modal_edit_title' => 'Webhook bearbeiten',
    // Delete confirmation
    'confirm_delete_header' => 'Webhook löschen',
    'confirm_delete_message' => 'Möchten Sie den Webhook „:name“ wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.',
    'delete_warning' => 'Diese Aktion kann nicht rückgängig gemacht werden.',
    // Toasts
    'created' => 'Webhook erfolgreich erstellt.',
    'updated' => 'Webhook erfolgreich aktualisiert.',
    'deleted' => 'Webhook erfolgreich gelöscht.',
    'error_load' => 'Webhooks konnten nicht geladen werden.',
    'error_save' => 'Webhook konnte nicht gespeichert werden.',
    'error_delete' => 'Webhook konnte nicht gelöscht werden.',
    // Secret badge
    'has_secret' => 'Geheimnis gesetzt',
    'no_secret' => 'Kein Geheimnis',
];
