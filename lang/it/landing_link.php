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

    'title' => 'Link aggiuntivi',
    'description' => 'Gestisci un elenco ordinato e libero di link aggiuntivi mostrati nella navigazione e/o nel piè di pagina della landing page.',

    // Empty state
    'no_links' => 'Nessun link aggiuntivo configurato.',
    'create_first' => 'Crea il tuo primo link',

    // Table columns
    'col_label' => 'Etichetta',
    'col_url' => 'URL',
    'col_placement' => 'Posizione',
    'col_enabled' => 'Attivo',
    'col_actions' => 'Azioni',

    // Placement labels
    'placement_nav' => 'Navigazione',
    'placement_footer' => 'Piè di pagina',
    'placement_both' => 'Navigazione e piè di pagina',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Predefinito',
    'built_in_cannot_delete' => 'Questo link predefinito non può essere eliminato. Disattivalo, oppure riordinalo come qualsiasi altro link.',
    'built_in_url_hint' => 'Questo è un link predefinito e la sua destinazione non può essere modificata.',

    // Buttons
    'create' => 'Crea link',
    'edit' => 'Modifica',
    'delete' => 'Elimina',
    'cancel' => 'Annulla',
    'save' => 'Salva',

    // Form fields
    'field_label' => 'Etichetta',
    'field_label_placeholder' => 'es. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Posizione',
    'field_open_in_new_tab' => 'Apri in una nuova scheda',
    'field_enabled' => 'Attivo',

    // Modal titles
    'modal_create_title' => 'Crea link',
    'modal_edit_title' => 'Modifica link',

    // Delete confirmation
    'confirm_delete_header' => 'Elimina link',
    'confirm_delete_message' => 'Vuoi davvero eliminare il link ":label"? Questa azione non può essere annullata.',

    // Toasts
    'created' => 'Link creato con successo.',
    'updated' => 'Link aggiornato con successo.',
    'deleted' => 'Link eliminato con successo.',
    'reordered' => 'Link riordinati con successo.',
    'error_load' => 'Impossibile caricare i link.',
    'error_save' => 'Impossibile salvare il link.',
    'error_delete' => 'Impossibile eliminare il link.',
    'error_reorder' => 'Impossibile riordinare i link.',
];
