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

    'title' => 'Extra länkar',
    'description' => 'Hantera en valfri, ordnad lista med extra länkar som visas i navigeringen och/eller sidfoten på startsidan.',

    // Empty state
    'no_links' => 'Inga extra länkar har konfigurerats ännu.',
    'create_first' => 'Skapa din första länk',

    // Table columns
    'col_label' => 'Etikett',
    'col_url' => 'URL',
    'col_placement' => 'Placering',
    'col_enabled' => 'Aktiverad',
    'col_actions' => 'Åtgärder',

    // Placement labels
    'placement_nav' => 'Navigering',
    'placement_footer' => 'Sidfot',
    'placement_both' => 'Navigering och sidfot',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Inbyggd',
    'built_in_cannot_delete' => 'Denna inbyggda länk kan inte raderas. Inaktivera den i stället, eller ändra dess ordning som för andra länkar.',
    'built_in_url_hint' => 'Detta är en inbyggd länk och dess mål kan inte ändras.',

    // Buttons
    'create' => 'Skapa länk',
    'edit' => 'Redigera',
    'delete' => 'Radera',
    'cancel' => 'Avbryt',
    'save' => 'Spara',

    // Form fields
    'field_label' => 'Etikett',
    'field_label_placeholder' => 't.ex. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Placering',
    'field_open_in_new_tab' => 'Öppna i ny flik',
    'field_enabled' => 'Aktiverad',

    // Modal titles
    'modal_create_title' => 'Skapa länk',
    'modal_edit_title' => 'Redigera länk',

    // Delete confirmation
    'confirm_delete_header' => 'Radera länk',
    'confirm_delete_message' => 'Är du säker på att du vill radera länken ":label"? Denna åtgärd kan inte ångras.',

    // Toasts
    'created' => 'Länken har skapats.',
    'updated' => 'Länken har uppdaterats.',
    'deleted' => 'Länken har raderats.',
    'reordered' => 'Länkarnas ordning har ändrats.',
    'error_load' => 'Det gick inte att läsa in länkar.',
    'error_save' => 'Det gick inte att spara länken.',
    'error_delete' => 'Det gick inte att radera länken.',
    'error_reorder' => 'Det gick inte att ändra länkarnas ordning.',
];
