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

    'title' => 'Modifica album in blocco',
    'description' => 'Modifica metadati e impostazioni di visibilità di più album contemporaneamente.',
    'warning' => 'Le modifiche effettuate qui vengono applicate immediatamente e non possono essere annullate. Gli album tag non vengono mostrati.',

    // Table columns
    'col_title' => 'Titolo',
    'col_owner' => 'Proprietario',
    'col_license' => 'Licenza',
    'col_is_nsfw' => 'Sensibile',
    'col_is_public' => 'Pubblico',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Foto completa',
    'col_grants_download' => 'Download',
    'col_grants_upload' => 'Caricamento',
    'col_photo_sorting' => 'Ordine foto',
    'col_album_sorting' => 'Ordine album',
    'col_created_at' => 'Creato',

    // Filter
    'filter_placeholder' => 'Cerca per titolo...',

    // Pagination
    'per_page' => 'Per pagina',
    'total_selected' => ':n album selezionato|:n album selezionati',
    'select_all_page' => 'Seleziona tutti in questa pagina',
    'select_all_matching' => 'Seleziona tutti i risultati',
    'cap_warning' => 'Sono stati selezionati solo i primi 1.000 album.',

    // Mode toggle
    'mode_paginated' => 'Paginato',
    'mode_infinite' => 'Scorrimento infinito',

    // Action buttons
    'action_delete' => 'Elimina',
    'action_set_owner' => 'Imposta proprietario',
    'action_edit_fields' => 'Modifica campi',

    // Edit Fields modal
    'edit_fields_title' => 'Modifica campi',
    'edit_fields_description' => 'Verranno aggiornati solo i campi selezionati. I valori vuoti cancellano il campo.',
    'section_metadata' => 'Metadati',
    'section_visibility' => 'Visibilità',
    'field_description' => 'Descrizione',
    'field_copyright' => 'Copyright',
    'field_license' => 'Licenza',
    'field_photo_layout' => 'Layout foto',
    'field_photo_sorting_col' => 'Colonna ordinamento foto',
    'field_photo_sorting_order' => 'Direzione ordinamento foto',
    'field_album_sorting_col' => 'Colonna ordinamento album',
    'field_album_sorting_order' => 'Direzione ordinamento album',
    'field_album_thumb_aspect_ratio' => 'Proporzioni miniatura',
    'field_album_timeline' => 'Timeline album',
    'field_photo_timeline' => 'Timeline foto',
    'field_published_at' => 'Data di pubblicazione in Flow',
    'field_is_nsfw' => 'Sensibile',
    'field_is_public' => 'Pubblico',
    'field_is_link_required' => 'Link obbligatorio',
    'field_grants_full_photo_access' => 'Accesso alla foto completa',
    'field_grants_download' => 'Download',
    'field_grants_upload' => 'Caricamento (SE)',
    'apply' => 'Applica',
    'cancel' => 'Annulla',

    // Set Owner modal
    'set_owner_title' => 'Imposta proprietario',
    'set_owner_description' => 'Tutti gli album selezionati verranno spostati al livello principale e anche i loro discendenti verranno trasferiti.',
    'set_owner_select_user' => 'Seleziona il nuovo proprietario',
    'transfer' => 'Trasferisci',

    // Delete confirmation modal
    'delete_title' => 'Elimina album',
    'delete_confirm' => 'Stai per eliminare definitivamente :count album con tutti i suoi sotto-album e foto. Questa azione non può essere annullata.|Stai per eliminare definitivamente :count album con tutti i loro sotto-album e foto. Questa azione non può essere annullata.',
    'confirm_delete' => 'Conferma eliminazione',

    // Toasts
    'success_patch' => 'Album aggiornati.',
    'success_set_owner' => 'Proprietà trasferita.',
    'success_delete' => 'Album eliminati.',
    'error_load' => 'Impossibile caricare gli album.',
    'error_load_ids' => 'Impossibile caricare gli ID degli album.',
    'error_patch' => 'Impossibile aggiornare gli album.',
    'error_missing_timezone' => 'Seleziona un fuso orario per la data di pubblicazione.',
    'error_set_owner' => 'Impossibile trasferire la proprietà.',
    'error_delete' => 'Impossibile eliminare gli album.',
    'error_load_users' => 'Impossibile caricare gli utenti.',
];
