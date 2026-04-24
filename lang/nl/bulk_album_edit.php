<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Bulk Album Edit',
    'description' => 'Edit metadata and visibility settings for multiple albums at once.',
    'warning' => 'Changes made here are applied immediately and cannot be undone. Tag albums are not shown.',

    // Table columns
    'col_title' => 'Title',
    'col_owner' => 'Owner',
    'col_license' => 'License',
    'col_is_nsfw' => 'Sensitive',
    'col_is_public' => 'Public',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Full Photo',
    'col_grants_download' => 'Download',
    'col_grants_upload' => 'Upload',
    'col_photo_sorting' => 'Photo Sort',
    'col_album_sorting' => 'Album Sort',
    'col_created_at' => 'Created',

    // Filter
    'filter_placeholder' => 'Search by title...',

    // Pagination
    'per_page' => 'Per page',
    'total_selected' => ':n album selected|:n albums selected',
    'select_all_page' => 'Select all on this page',
    'select_all_matching' => 'Select all matching',
    'cap_warning' => 'Only the first 1,000 albums have been selected.',

    // Mode toggle
    'mode_paginated' => 'Paginated',
    'mode_infinite' => 'Infinite scroll',

    // Action buttons
    'action_delete' => 'Delete',
    'action_set_owner' => 'Set Owner',
    'action_edit_fields' => 'Edit Fields',

    // Edit Fields modal
    'edit_fields_title' => 'Edit Fields',
    'edit_fields_description' => 'Only checked fields will be updated. Empty values clear the field.',
    'section_metadata' => 'Metadata',
    'section_visibility' => 'Visibility',
    'field_description' => 'Description',
    'field_copyright' => 'Copyright',
    'field_license' => 'License',
    'field_photo_layout' => 'Photo Layout',
    'field_photo_sorting_col' => 'Photo Sort Column',
    'field_photo_sorting_order' => 'Photo Sort Order',
    'field_album_sorting_col' => 'Album Sort Column',
    'field_album_sorting_order' => 'Album Sort Order',
    'field_album_thumb_aspect_ratio' => 'Thumb Aspect Ratio',
    'field_album_timeline' => 'Album Timeline',
    'field_photo_timeline' => 'Photo Timeline',
    'field_is_nsfw' => 'Sensitive',
    'field_is_public' => 'Public',
    'field_is_link_required' => 'Link Required',
    'field_grants_full_photo_access' => 'Full Photo Access',
    'field_grants_download' => 'Download',
    'field_grants_upload' => 'Upload (SE)',
    'apply' => 'Apply',
    'cancel' => 'Cancel',

    // Set Owner modal
    'set_owner_title' => 'Set Owner',
    'set_owner_description' => 'All selected albums will be moved to the root level and their descendants will also be transferred.',
    'set_owner_select_user' => 'Select new owner',
    'transfer' => 'Transfer',

    // Delete confirmation modal
    'delete_title' => 'Delete Albums',
    'delete_confirm' => 'You are about to permanently delete :count album and all its sub-albums and photos. This action cannot be undone.|You are about to permanently delete :count albums and all their sub-albums and photos. This action cannot be undone.',
    'confirm_delete' => 'Confirm Delete',

    // Toasts
    'success_patch' => 'Albums updated successfully.',
    'success_set_owner' => 'Ownership transferred successfully.',
    'success_delete' => 'Albums deleted successfully.',
    'error_load' => 'Failed to load albums.',
    'error_load_ids' => 'Failed to load album IDs.',
    'error_patch' => 'Failed to update albums.',
    'error_set_owner' => 'Failed to transfer ownership.',
    'error_delete' => 'Failed to delete albums.',
    'error_load_users' => 'Failed to load users.',
];
