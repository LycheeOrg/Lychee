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

    'title' => 'Dodatkowe linki',
    'description' => 'Zarządzaj dowolną, uporządkowaną listą dodatkowych linków wyświetlanych w nawigacji i/lub stopce strony głównej.',

    // Empty state
    'no_links' => 'Nie skonfigurowano jeszcze dodatkowych linków.',
    'create_first' => 'Utwórz pierwszy link',

    // Table columns
    'col_label' => 'Etykieta',
    'col_url' => 'URL',
    'col_placement' => 'Umiejscowienie',
    'col_enabled' => 'Włączony',
    'col_actions' => 'Akcje',

    // Placement labels
    'placement_nav' => 'Nawigacja',
    'placement_footer' => 'Stopka',
    'placement_both' => 'Nawigacja i stopka',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Wbudowany',
    'built_in_cannot_delete' => 'Tego wbudowanego linku nie można usunąć. Możesz go wyłączyć lub zmienić jego kolejność jak każdego innego linku.',
    'built_in_url_hint' => 'To jest wbudowany link i nie można zmienić jego celu.',

    // Buttons
    'create' => 'Utwórz link',
    'edit' => 'Edytuj',
    'delete' => 'Usuń',
    'cancel' => 'Anuluj',
    'save' => 'Zapisz',

    // Form fields
    'field_label' => 'Etykieta',
    'field_label_placeholder' => 'np. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Umiejscowienie',
    'field_open_in_new_tab' => 'Otwórz w nowej karcie',
    'field_enabled' => 'Włączony',

    // Modal titles
    'modal_create_title' => 'Utwórz link',
    'modal_edit_title' => 'Edytuj link',

    // Delete confirmation
    'confirm_delete_header' => 'Usuń link',
    'confirm_delete_message' => 'Czy na pewno chcesz usunąć link ":label"? Tej operacji nie można cofnąć.',

    // Toasts
    'created' => 'Link został utworzony.',
    'updated' => 'Link został zaktualizowany.',
    'deleted' => 'Link został usunięty.',
    'reordered' => 'Kolejność linków została zmieniona.',
    'error_load' => 'Nie udało się wczytać linków.',
    'error_save' => 'Nie udało się zapisać linku.',
    'error_delete' => 'Nie udało się usunąć linku.',
    'error_reorder' => 'Nie udało się zmienić kolejności linków.',
];
