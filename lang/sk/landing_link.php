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

    'title' => 'Ďalšie odkazy',
    'description' => 'Spravujte ľubovoľný zoradený zoznam ďalších odkazov zobrazených v navigácii a/alebo pätičke úvodnej stránky.',

    // Empty state
    'no_links' => 'Zatiaľ nie sú nakonfigurované žiadne ďalšie odkazy.',
    'create_first' => 'Vytvorte svoj prvý odkaz',

    // Table columns
    'col_label' => 'Popis',
    'col_url' => 'URL',
    'col_placement' => 'Umiestnenie',
    'col_enabled' => 'Zapnuté',
    'col_actions' => 'Akcie',

    // Placement labels
    'placement_nav' => 'Navigácia',
    'placement_footer' => 'Pätička',
    'placement_both' => 'Navigácia a pätička',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Vstavaný',
    'built_in_cannot_delete' => 'Tento vstavaný odkaz nie je možné odstrániť. Namiesto toho ho vypnite alebo ho preusporiadajte ako akýkoľvek iný odkaz.',
    'built_in_url_hint' => 'Toto je vstavaný odkaz a jeho cieľ nie je možné zmeniť.',

    // Buttons
    'create' => 'Vytvoriť odkaz',
    'edit' => 'Upraviť',
    'delete' => 'Odstrániť',
    'cancel' => 'Zrušiť',
    'save' => 'Uložiť',

    // Form fields
    'field_label' => 'Popis',
    'field_label_placeholder' => 'napr. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Umiestnenie',
    'field_open_in_new_tab' => 'Otvoriť na novej karte',
    'field_enabled' => 'Zapnuté',

    // Modal titles
    'modal_create_title' => 'Vytvoriť odkaz',
    'modal_edit_title' => 'Upraviť odkaz',

    // Delete confirmation
    'confirm_delete_header' => 'Odstrániť odkaz',
    'confirm_delete_message' => 'Naozaj chcete odstrániť odkaz ":label"? Túto akciu nie je možné vrátiť späť.',

    // Toasts
    'created' => 'Odkaz bol úspešne vytvorený.',
    'updated' => 'Odkaz bol úspešne aktualizovaný.',
    'deleted' => 'Odkaz bol úspešne odstránený.',
    'reordered' => 'Odkazy boli úspešne preusporiadané.',
    'error_load' => 'Odkazy sa nepodarilo načítať.',
    'error_save' => 'Odkaz sa nepodarilo uložiť.',
    'error_delete' => 'Odkaz sa nepodarilo odstrániť.',
    'error_reorder' => 'Odkazy sa nepodarilo preusporiadať.',
];
