<?php
return [
    /**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */
    'title' => 'Album-Massenbearbeitung',
    'description' => 'Bearbeiten Sie Metadaten und Sichtbarkeitseinstellungen für mehrere Alben gleichzeitig.',
    'warning' => 'Die hier vorgenommenen Änderungen werden sofort übernommen und können nicht rückgängig gemacht werden. Tag-Alben werden nicht angezeigt.',
    // Table columns
    'col_title' => 'Titel',
    'col_owner' => 'Eigentümer',
    'col_license' => 'Lizenz',
    'col_is_nsfw' => 'Empfindlich',
    'col_is_public' => 'Öffentlich',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Foto in voller Größe',
    'col_grants_download' => 'Herunterladen',
    'col_grants_upload' => 'Hochladen',
    'col_photo_sorting' => 'Fotosortierung',
    'col_album_sorting' => 'Album „Sort“',
    'col_created_at' => 'Erstellt',
    // Filter
    'filter_placeholder' => 'Nach Titel suchen...',
    // Pagination
    'per_page' => 'Für Seite',
    'total_selected' => ':n Album ausgewählt|:n albums selected',
    'select_all_page' => 'Alles auf dieser Seite auswählen',
    'select_all_matching' => 'Alle Übereinstimmungen auswählen',
    'cap_warning' => 'Es wurden nur die ersten 1.000 Alben ausgewählt.',
    // Mode toggle
    'mode_paginated' => 'Paginiert',
    'mode_infinite' => 'Unendliches Scrollen',
    // Action buttons
    'action_delete' => 'Löschen',
    'action_set_owner' => 'Eigentümer festlegen',
    'action_edit_fields' => 'Felder bearbeiten',
    // Edit Fields modal
    'edit_fields_title' => 'Felder bearbeiten',
    'edit_fields_description' => 'Es werden nur die markierten Felder aktualisiert. Leere Werte löschen den Inhalt des Feldes.',
    'section_metadata' => 'Metadaten',
    'section_visibility' => 'Sichtbarkeit',
    'field_description' => 'Beschreibung',
    'field_copyright' => 'Urheberrecht',
    'field_license' => 'Lizenz',
    'field_photo_layout' => 'Fotolayout',
    'field_photo_sorting_col' => 'Spalte „Fotos sortieren“',
    'field_photo_sorting_order' => 'Sortierreihenfolge der Fotos',
    'field_album_sorting_col' => 'Spalte „Album sortieren“',
    'field_album_sorting_order' => 'Sortierreihenfolge der Alben',
    'field_album_thumb_aspect_ratio' => 'Seitenverhältnis des Vorschaubilds',
    'field_album_timeline' => 'Album-Chronologie',
    'field_photo_timeline' => 'Foto-Zeitleiste',
    'field_is_nsfw' => 'Empfindlich',
    'field_is_public' => 'Öffentlich',
    'field_is_link_required' => 'Link erforderlich',
    'field_grants_full_photo_access' => 'Voller Zugriff auf Fotos',
    'field_grants_download' => 'Herunterladen',
    'field_grants_upload' => 'Hochladen (SE)',
    'apply' => 'Anwenden',
    'cancel' => 'Abbrechen',
    // Set Owner modal
    'set_owner_title' => 'Besitzer setzen',
    'set_owner_description' => 'Alle ausgewählten Alben werden in die oberste Ebene verschoben, und ihre untergeordneten Elemente werden ebenfalls übertragen.',
    'set_owner_select_user' => 'Neuen Eigentümer auswählen',
    'transfer' => 'Übertragung',
    // Delete confirmation modal
    'delete_title' => 'Alben löschen',
    'delete_confirm' => 'Sie sind dabei, das Album „:count“ sowie alle darin enthaltenen Unteralben und Fotos endgültig zu löschen. Dieser Vorgang kann nicht rückgängig gemacht werden.|You are about to permanently delete :count albums and all their sub-albums and photos. This action cannot be undone.',
    'confirm_delete' => 'Löschen bestätigen',
    // Toasts
    'success_patch' => 'Die Alben wurden erfolgreich aktualisiert.',
    'success_set_owner' => 'Das Eigentumsrecht wurde erfolgreich übertragen.',
    'success_delete' => 'Die Alben wurden erfolgreich gelöscht.',
    'error_load' => 'Alben konnten nicht geladen werden.',
    'error_load_ids' => 'Das Laden der Album-IDs ist fehlgeschlagen.',
    'error_patch' => 'Die Aktualisierung der Alben ist fehlgeschlagen.',
    'error_set_owner' => 'Die Eigentumsübertragung ist fehlgeschlagen.',
    'error_delete' => 'Das Löschen der Alben ist fehlgeschlagen.',
    'error_load_users' => 'Das Laden der Benutzer ist fehlgeschlagen.',
];
