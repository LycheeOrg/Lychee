<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => 'Extra Links',
    'description' => 'Manage an arbitrary, ordered list of extra links shown on the nav and/or footer of the landing page.',

    // Empty state
    'no_links' => 'No extra links configured yet.',
    'create_first' => 'Create your first link',

    // Table columns
    'col_label' => 'Label',
    'col_url' => 'URL',
    'col_placement' => 'Placement',
    'col_enabled' => 'Enabled',
    'col_actions' => 'Actions',

    // Placement labels
    'placement_nav' => 'Nav',
    'placement_footer' => 'Footer',
    'placement_both' => 'Nav & Footer',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Built-in',
    'built_in_cannot_delete' => 'This built-in link cannot be deleted. Disable it instead, or reorder it like any other link.',
    'built_in_url_hint' => 'This is a built-in link and its target cannot be changed.',

    // Buttons
    'create' => 'Create Link',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'save' => 'Save',

    // Form fields
    'field_label' => 'Label',
    'field_label_placeholder' => 'e.g. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Placement',
    'field_open_in_new_tab' => 'Open in new tab',
    'field_enabled' => 'Enabled',

    // Modal titles
    'modal_create_title' => 'Create Link',
    'modal_edit_title' => 'Edit Link',

    // Delete confirmation
    'confirm_delete_header' => 'Delete Link',
    'confirm_delete_message' => 'Are you sure you want to delete the link ":label"? This action cannot be undone.',

    // Toasts
    'created' => 'Link created successfully.',
    'updated' => 'Link updated successfully.',
    'deleted' => 'Link deleted successfully.',
    'reordered' => 'Links reordered successfully.',
    'error_load' => 'Failed to load links.',
    'error_save' => 'Failed to save link.',
    'error_delete' => 'Failed to delete link.',
    'error_reorder' => 'Failed to reorder links.',
];
