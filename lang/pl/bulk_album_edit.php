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

    'title' => 'Zbiorcza edycja albumów',
    'description' => 'Edytuj metadane i ustawienia widoczności wielu albumów jednocześnie.',
    'warning' => 'Zmiany wprowadzone tutaj są stosowane natychmiast i nie można ich cofnąć. Albumy tagów nie są wyświetlane.',

    // Table columns
    'col_title' => 'Tytuł',
    'col_owner' => 'Właściciel',
    'col_license' => 'Licencja',
    'col_is_nsfw' => 'Wrażliwy',
    'col_is_public' => 'Publiczny',
    'col_is_link_required' => 'Link',
    'col_grants_full_photo_access' => 'Pełne zdjęcie',
    'col_grants_download' => 'Pobieranie',
    'col_grants_upload' => 'Przesyłanie',
    'col_photo_sorting' => 'Sortowanie zdjęć',
    'col_album_sorting' => 'Sortowanie albumów',
    'col_created_at' => 'Utworzono',
    'taken_range' => 'Wykonano: :range',
    'taken_none' => 'Brak danych',

    // Filter
    'filter_placeholder' => 'Szukaj po tytule...',

    // Pagination
    'per_page' => 'Na stronę',
    'total_selected' => 'Wybrano :n album|Wybrano albumów: :n',
    'select_all_page' => 'Zaznacz wszystkie na tej stronie',
    'select_all_matching' => 'Zaznacz wszystkie pasujące',
    'cap_warning' => 'Zaznaczono tylko pierwsze 1000 albumów.',

    // Mode toggle
    'mode_paginated' => 'Stronicowanie',
    'mode_infinite' => 'Przewijanie nieskończone',

    // Action buttons
    'action_delete' => 'Usuń',
    'action_set_owner' => 'Ustaw właściciela',
    'action_edit_fields' => 'Edytuj pola',

    // Edit Fields modal
    'edit_fields_title' => 'Edytuj pola',
    'edit_fields_description' => 'Zaktualizowane zostaną tylko zaznaczone pola. Puste wartości czyszczą pole.',
    'section_metadata' => 'Metadane',
    'section_visibility' => 'Widoczność',
    'field_description' => 'Opis',
    'field_copyright' => 'Prawa autorskie',
    'field_license' => 'Licencja',
    'field_photo_layout' => 'Układ zdjęć',
    'field_photo_sorting_col' => 'Kolumna sortowania zdjęć',
    'field_photo_sorting_order' => 'Kolejność sortowania zdjęć',
    'field_album_sorting_col' => 'Kolumna sortowania albumów',
    'field_album_sorting_order' => 'Kolejność sortowania albumów',
    'field_album_thumb_aspect_ratio' => 'Proporcje miniatury',
    'field_album_timeline' => 'Oś czasu albumów',
    'field_photo_timeline' => 'Oś czasu zdjęć',
    'field_published_at' => 'Data publikacji we Flow',
    'field_is_nsfw' => 'Wrażliwy',
    'field_is_public' => 'Publiczny',
    'field_is_link_required' => 'Wymagany link',
    'field_grants_full_photo_access' => 'Pełny dostęp do zdjęć',
    'field_grants_download' => 'Pobieranie',
    'field_grants_upload' => 'Przesyłanie (SE)',
    'apply' => 'Zastosuj',
    'cancel' => 'Anuluj',

    // Set Owner modal
    'set_owner_title' => 'Ustaw właściciela',
    'set_owner_description' => 'Wszystkie wybrane albumy zostaną przeniesione na poziom główny, a ich elementy podrzędne również zostaną przekazane.',
    'set_owner_select_user' => 'Wybierz nowego właściciela',
    'transfer' => 'Przekaż',

    // Delete confirmation modal
    'delete_title' => 'Usuń albumy',
    'delete_confirm' => 'Zamierzasz trwale usunąć :count album wraz ze wszystkimi podalbumami i zdjęciami. Tej operacji nie można cofnąć.|Zamierzasz trwale usunąć albumy (:count) wraz ze wszystkimi ich podalbumami i zdjęciami. Tej operacji nie można cofnąć.',
    'confirm_delete' => 'Potwierdź usunięcie',

    // Toasts
    'success_patch' => 'Albumy zostały zaktualizowane.',
    'success_set_owner' => 'Własność została przekazana.',
    'success_delete' => 'Albumy zostały usunięte.',
    'error_load' => 'Nie udało się wczytać albumów.',
    'error_load_ids' => 'Nie udało się wczytać identyfikatorów albumów.',
    'error_patch' => 'Nie udało się zaktualizować albumów.',
    'error_missing_timezone' => 'Wybierz strefę czasową dla daty publikacji.',
    'error_set_owner' => 'Nie udało się przekazać własności.',
    'error_delete' => 'Nie udało się usunąć albumów.',
    'error_load_users' => 'Nie udało się wczytać użytkowników.',
];
