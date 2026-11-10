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

    'title' => 'További hivatkozások',
    'description' => 'A nyitóoldal navigációs sávjában és/vagy láblécében megjelenő további hivatkozások tetszőleges, rendezett listájának kezelése.',

    // Empty state
    'no_links' => 'Még nincsenek beállított további hivatkozások.',
    'create_first' => 'Hozza létre első hivatkozását',

    // Table columns
    'col_label' => 'Felirat',
    'col_url' => 'URL',
    'col_placement' => 'Elhelyezés',
    'col_enabled' => 'Engedélyezve',
    'col_actions' => 'Műveletek',

    // Placement labels
    'placement_nav' => 'Navigáció',
    'placement_footer' => 'Lábléc',
    'placement_both' => 'Navigáció és lábléc',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Beépített',
    'built_in_cannot_delete' => 'Ez a beépített hivatkozás nem törölhető. Helyette tiltsa le, vagy rendezze át, mint bármely más hivatkozást.',
    'built_in_url_hint' => 'Ez egy beépített hivatkozás, a célja nem módosítható.',

    // Buttons
    'create' => 'Hivatkozás létrehozása',
    'edit' => 'Szerkesztés',
    'delete' => 'Törlés',
    'cancel' => 'Mégse',
    'save' => 'Mentés',

    // Form fields
    'field_label' => 'Felirat',
    'field_label_placeholder' => 'pl. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Elhelyezés',
    'field_open_in_new_tab' => 'Megnyitás új lapon',
    'field_enabled' => 'Engedélyezve',

    // Modal titles
    'modal_create_title' => 'Hivatkozás létrehozása',
    'modal_edit_title' => 'Hivatkozás szerkesztése',

    // Delete confirmation
    'confirm_delete_header' => 'Hivatkozás törlése',
    'confirm_delete_message' => 'Biztosan törli a(z) ":label" hivatkozást? Ez a művelet nem vonható vissza.',

    // Toasts
    'created' => 'A hivatkozás létrehozása sikeres.',
    'updated' => 'A hivatkozás frissítése sikeres.',
    'deleted' => 'A hivatkozás törlése sikeres.',
    'reordered' => 'A hivatkozások átrendezése sikeres.',
    'error_load' => 'Nem sikerült betölteni a hivatkozásokat.',
    'error_save' => 'Nem sikerült menteni a hivatkozást.',
    'error_delete' => 'Nem sikerült törölni a hivatkozást.',
    'error_reorder' => 'Nem sikerült átrendezni a hivatkozásokat.',
];
