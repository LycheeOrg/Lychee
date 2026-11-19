<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => 'Skonfiguruj wychodzące webhooki wywoływane przy dodawaniu, przenoszeniu lub usuwaniu zdjęć.',

    // Empty state
    'no_webhooks' => 'Nie skonfigurowano jeszcze żadnych webhooków.',
    'create_first' => 'Utwórz pierwszy webhook',

    // Table columns
    'col_name' => 'Nazwa',
    'col_event' => 'Zdarzenie',
    'col_method' => 'Metoda',
    'col_url' => 'URL',
    'col_format' => 'Format',
    'col_enabled' => 'Włączony',
    'col_actions' => 'Akcje',

    // Event labels
    'event_photo_add' => 'Dodano zdjęcie',
    'event_photo_move' => 'Przeniesiono zdjęcie',
    'event_photo_delete' => 'Usunięto zdjęcie',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query string',

    // Buttons
    'create' => 'Utwórz webhook',
    'edit' => 'Edytuj',
    'delete' => 'Usuń',
    'cancel' => 'Anuluj',
    'save' => 'Zapisz',

    // Form fields
    'field_name' => 'Nazwa',
    'field_name_placeholder' => 'np. Mój webhook',
    'field_event' => 'Zdarzenie',
    'field_method' => 'Metoda HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Format payloadu',
    'field_enabled' => 'Włączony',
    'field_secret' => 'Sekret',
    'field_secret_placeholder' => 'Pozostaw puste, aby zachować obecny sekret',
    'field_secret_header' => 'Nagłówek sekretu',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Wysyłaj ID zdjęcia',
    'field_send_album_id' => 'Wysyłaj ID albumu',
    'field_send_title' => 'Wysyłaj tytuł',
    'field_send_size_variants' => 'Wysyłaj warianty rozmiaru',

    // Modal titles
    'modal_create_title' => 'Utwórz webhook',
    'modal_edit_title' => 'Edytuj webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Usuń webhook',
    'confirm_delete_message' => 'Czy na pewno chcesz usunąć webhook ":name"? Tej operacji nie można cofnąć.',
    'delete_warning' => 'Tej operacji nie można cofnąć.',

    // Toasts
    'created' => 'Webhook został utworzony.',
    'updated' => 'Webhook został zaktualizowany.',
    'deleted' => 'Webhook został usunięty.',
    'error_load' => 'Nie udało się wczytać webhooków.',
    'error_save' => 'Nie udało się zapisać webhooka.',
    'error_delete' => 'Nie udało się usunąć webhooka.',

    // Secret badge
    'has_secret' => 'Sekret ustawiony',
    'no_secret' => 'Brak sekretu',
];
