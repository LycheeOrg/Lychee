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

    'title' => 'Zusätzliche Links',
    'description' => 'Verwalten Sie eine beliebige, geordnete Liste zusätzlicher Links, die in der Navigation und/oder Fußzeile der Startseite angezeigt werden.',

    // Empty state
    'no_links' => 'Noch keine zusätzlichen Links konfiguriert.',
    'create_first' => 'Ersten Link erstellen',

    // Table columns
    'col_label' => 'Beschriftung',
    'col_url' => 'URL',
    'col_placement' => 'Platzierung',
    'col_enabled' => 'Aktiviert',
    'col_actions' => 'Aktionen',

    // Placement labels
    'placement_nav' => 'Nav',
    'placement_footer' => 'Fußzeile',
    'placement_both' => 'Nav & Fußzeile',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Integriert',
    'built_in_cannot_delete' => 'Dieser integrierte Link kann nicht gelöscht werden. Deaktivieren Sie ihn stattdessen oder ordnen Sie ihn wie jeden anderen Link neu an.',
    'built_in_url_hint' => 'Dies ist ein integrierter Link, sein Ziel kann nicht geändert werden.',

    // Buttons
    'create' => 'Link erstellen',
    'edit' => 'Bearbeiten',
    'delete' => 'Löschen',
    'cancel' => 'Abbrechen',
    'save' => 'Speichern',

    // Form fields
    'field_label' => 'Beschriftung',
    'field_label_placeholder' => 'z. B. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Platzierung',
    'field_open_in_new_tab' => 'In neuem Tab öffnen',
    'field_enabled' => 'Aktiviert',

    // Modal titles
    'modal_create_title' => 'Link erstellen',
    'modal_edit_title' => 'Link bearbeiten',

    // Delete confirmation
    'confirm_delete_header' => 'Link löschen',
    'confirm_delete_message' => 'Möchten Sie den Link ":label" wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.',

    // Toasts
    'created' => 'Link erfolgreich erstellt.',
    'updated' => 'Link erfolgreich aktualisiert.',
    'deleted' => 'Link erfolgreich gelöscht.',
    'reordered' => 'Links erfolgreich neu angeordnet.',
    'error_load' => 'Links konnten nicht geladen werden.',
    'error_save' => 'Link konnte nicht gespeichert werden.',
    'error_delete' => 'Link konnte nicht gelöscht werden.',
    'error_reorder' => 'Links konnten nicht neu angeordnet werden.',
];
