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
    'description' => 'Ρυθμίστε εξερχόμενα webhooks που ενεργοποιούνται όταν προστίθενται, μετακινούνται ή διαγράφονται φωτογραφίες.',

    // Empty state
    'no_webhooks' => 'Δεν έχουν ρυθμιστεί ακόμη webhooks.',
    'create_first' => 'Δημιουργήστε το πρώτο σας webhook',

    // Table columns
    'col_name' => 'Όνομα',
    'col_event' => 'Συμβάν',
    'col_method' => 'Μέθοδος',
    'col_url' => 'URL',
    'col_format' => 'Μορφή',
    'col_enabled' => 'Ενεργό',
    'col_actions' => 'Ενέργειες',

    // Event labels
    'event_photo_add' => 'Προσθήκη φωτογραφίας',
    'event_photo_move' => 'Μετακίνηση φωτογραφίας',
    'event_photo_delete' => 'Διαγραφή φωτογραφίας',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => 'Query String',

    // Buttons
    'create' => 'Δημιουργία webhook',
    'edit' => 'Επεξεργασία',
    'delete' => 'Διαγραφή',
    'cancel' => 'Ακύρωση',
    'save' => 'Αποθήκευση',

    // Form fields
    'field_name' => 'Όνομα',
    'field_name_placeholder' => 'π.χ. Το webhook μου',
    'field_event' => 'Συμβάν',
    'field_method' => 'Μέθοδος HTTP',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Μορφή payload',
    'field_enabled' => 'Ενεργό',
    'field_secret' => 'Μυστικό',
    'field_secret_placeholder' => 'Αφήστε κενό για να διατηρηθεί το υπάρχον μυστικό',
    'field_secret_header' => 'Κεφαλίδα μυστικού',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => 'Αποστολή ID φωτογραφίας',
    'field_send_album_id' => 'Αποστολή ID άλμπουμ',
    'field_send_title' => 'Αποστολή τίτλου',
    'field_send_size_variants' => 'Αποστολή παραλλαγών μεγέθους',

    // Modal titles
    'modal_create_title' => 'Δημιουργία webhook',
    'modal_edit_title' => 'Επεξεργασία webhook',

    // Delete confirmation
    'confirm_delete_header' => 'Διαγραφή webhook',
    'confirm_delete_message' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε το webhook ":name"; Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.',
    'delete_warning' => 'Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.',

    // Toasts
    'created' => 'Το webhook δημιουργήθηκε επιτυχώς.',
    'updated' => 'Το webhook ενημερώθηκε επιτυχώς.',
    'deleted' => 'Το webhook διαγράφηκε επιτυχώς.',
    'error_load' => 'Αποτυχία φόρτωσης των webhooks.',
    'error_save' => 'Αποτυχία αποθήκευσης του webhook.',
    'error_delete' => 'Αποτυχία διαγραφής του webhook.',

    // Secret badge
    'has_secret' => 'Έχει οριστεί μυστικό',
    'no_secret' => 'Χωρίς μυστικό',
];
