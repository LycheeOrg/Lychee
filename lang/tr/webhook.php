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
    'description' => 'Configure outgoing webhooks that are triggered when photos are added, moved, or deleted.',

    // Empty state
    'no_webhooks' => 'No webhooks configured yet.',
    'create_first' => 'Create your first webhook',

    // Table columns
    'col_name' => 'Name',
    'col_event' => 'Event',
    'col_method' => 'Method',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Enabled',
    'col_actions' => 'Actions',

    // Event labels
    'event_photo_add' => 'Photo Added',
    'event_photo_move' => 'Photo Moved',
    'event_photo_delete' => 'Photo Deleted',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query String',

    // Buttons
    'create' => 'Create Webhook',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'save' => 'Save',

    // Form fields
    'field_name' => 'Name',
    'field_name_placeholder' => 'e.g. My Webhook',
    'field_event' => 'Event',
    'field_method' => 'HTTP Method',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload Format',
    'field_enabled' => 'Enabled',
    'field_secret' => 'Secret',
    'field_secret_placeholder' => 'Leave empty to keep existing secret',
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
