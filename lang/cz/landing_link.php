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

    'title' => 'Další odkazy',
    'description' => 'Spravujte libovolný seřazený seznam dalších odkazů zobrazených v navigaci a/nebo zápatí úvodní stránky.',

    // Empty state
    'no_links' => 'Zatím nejsou nastaveny žádné další odkazy.',
    'create_first' => 'Vytvořte svůj první odkaz',

    // Table columns
    'col_label' => 'Popisek',
    'col_url' => 'URL',
    'col_placement' => 'Umístění',
    'col_enabled' => 'Povoleno',
    'col_actions' => 'Akce',

    // Placement labels
    'placement_nav' => 'Navigace',
    'placement_footer' => 'Zápatí',
    'placement_both' => 'Navigace a zápatí',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Vestavěný',
    'built_in_cannot_delete' => 'Tento vestavěný odkaz nelze smazat. Můžete ho zakázat nebo změnit jeho pořadí jako u jakéhokoli jiného odkazu.',
    'built_in_url_hint' => 'Toto je vestavěný odkaz a jeho cíl nelze změnit.',

    // Buttons
    'create' => 'Vytvořit odkaz',
    'edit' => 'Upravit',
    'delete' => 'Smazat',
    'cancel' => 'Zrušit',
    'save' => 'Uložit',

    // Form fields
    'field_label' => 'Popisek',
    'field_label_placeholder' => 'např. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Umístění',
    'field_open_in_new_tab' => 'Otevřít v nové kartě',
    'field_enabled' => 'Povoleno',

    // Modal titles
    'modal_create_title' => 'Vytvořit odkaz',
    'modal_edit_title' => 'Upravit odkaz',

    // Delete confirmation
    'confirm_delete_header' => 'Smazat odkaz',
    'confirm_delete_message' => 'Opravdu chcete smazat odkaz ":label"? Tuto akci nelze vrátit zpět.',

    // Toasts
    'created' => 'Odkaz byl úspěšně vytvořen.',
    'updated' => 'Odkaz byl úspěšně aktualizován.',
    'deleted' => 'Odkaz byl úspěšně smazán.',
    'reordered' => 'Pořadí odkazů bylo úspěšně změněno.',
    'error_load' => 'Odkazy se nepodařilo načíst.',
    'error_save' => 'Odkaz se nepodařilo uložit.',
    'error_delete' => 'Odkaz se nepodařilo smazat.',
    'error_reorder' => 'Pořadí odkazů se nepodařilo změnit.',
];
