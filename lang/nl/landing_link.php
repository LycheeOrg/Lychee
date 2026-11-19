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

    'title' => 'Extra links',
    'description' => 'Beheer een vrije, geordende lijst met extra links die in de navigatie en/of de footer van de landingspagina worden getoond.',

    // Empty state
    'no_links' => 'Nog geen extra links geconfigureerd.',
    'create_first' => 'Maak uw eerste link aan',

    // Table columns
    'col_label' => 'Label',
    'col_url' => 'URL',
    'col_placement' => 'Plaatsing',
    'col_enabled' => 'Ingeschakeld',
    'col_actions' => 'Acties',

    // Placement labels
    'placement_nav' => 'Navigatie',
    'placement_footer' => 'Footer',
    'placement_both' => 'Navigatie & footer',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Ingebouwd',
    'built_in_cannot_delete' => 'Deze ingebouwde link kan niet worden verwijderd. Schakel hem in plaats daarvan uit, of wijzig de volgorde zoals bij elke andere link.',
    'built_in_url_hint' => 'Dit is een ingebouwde link; het doel ervan kan niet worden gewijzigd.',

    // Buttons
    'create' => 'Link aanmaken',
    'edit' => 'Bewerken',
    'delete' => 'Verwijderen',
    'cancel' => 'Annuleren',
    'save' => 'Opslaan',

    // Form fields
    'field_label' => 'Label',
    'field_label_placeholder' => 'bijv. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Plaatsing',
    'field_open_in_new_tab' => 'In nieuw tabblad openen',
    'field_enabled' => 'Ingeschakeld',

    // Modal titles
    'modal_create_title' => 'Link aanmaken',
    'modal_edit_title' => 'Link bewerken',

    // Delete confirmation
    'confirm_delete_header' => 'Link verwijderen',
    'confirm_delete_message' => 'Weet u zeker dat u de link ":label" wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.',

    // Toasts
    'created' => 'Link succesvol aangemaakt.',
    'updated' => 'Link succesvol bijgewerkt.',
    'deleted' => 'Link succesvol verwijderd.',
    'reordered' => 'Volgorde van links succesvol gewijzigd.',
    'error_load' => 'Laden van links mislukt.',
    'error_save' => 'Opslaan van link mislukt.',
    'error_delete' => 'Verwijderen van link mislukt.',
    'error_reorder' => 'Wijzigen van volgorde van links mislukt.',
];
