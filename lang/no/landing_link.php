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

    'title' => 'Ekstra lenker',
    'description' => 'Administrer en vilkårlig, ordnet liste med ekstra lenker som vises i navigasjonen og/eller bunnteksten på landingssiden.',

    // Empty state
    'no_links' => 'Ingen ekstra lenker er konfigurert ennå.',
    'create_first' => 'Opprett din første lenke',

    // Table columns
    'col_label' => 'Etikett',
    'col_url' => 'URL',
    'col_placement' => 'Plassering',
    'col_enabled' => 'Aktivert',
    'col_actions' => 'Handlinger',

    // Placement labels
    'placement_nav' => 'Navigasjon',
    'placement_footer' => 'Bunntekst',
    'placement_both' => 'Navigasjon og bunntekst',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Innebygd',
    'built_in_cannot_delete' => 'Denne innebygde lenken kan ikke slettes. Deaktiver den i stedet, eller endre rekkefølgen som for andre lenker.',
    'built_in_url_hint' => 'Dette er en innebygd lenke, og målet kan ikke endres.',

    // Buttons
    'create' => 'Opprett lenke',
    'edit' => 'Rediger',
    'delete' => 'Slett',
    'cancel' => 'Avbryt',
    'save' => 'Lagre',

    // Form fields
    'field_label' => 'Etikett',
    'field_label_placeholder' => 'f.eks. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Plassering',
    'field_open_in_new_tab' => 'Åpne i ny fane',
    'field_enabled' => 'Aktivert',

    // Modal titles
    'modal_create_title' => 'Opprett lenke',
    'modal_edit_title' => 'Rediger lenke',

    // Delete confirmation
    'confirm_delete_header' => 'Slett lenke',
    'confirm_delete_message' => 'Er du sikker på at du vil slette lenken ":label"? Denne handlingen kan ikke angres.',

    // Toasts
    'created' => 'Lenken ble opprettet.',
    'updated' => 'Lenken ble oppdatert.',
    'deleted' => 'Lenken ble slettet.',
    'reordered' => 'Rekkefølgen på lenkene ble endret.',
    'error_load' => 'Kunne ikke laste lenker.',
    'error_save' => 'Kunne ikke lagre lenken.',
    'error_delete' => 'Kunne ikke slette lenken.',
    'error_reorder' => 'Kunne ikke endre rekkefølgen på lenkene.',
];
