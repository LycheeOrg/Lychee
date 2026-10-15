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

    'title' => 'Μαζική επεξεργασία άλμπουμ',
    'description' => 'Επεξεργαστείτε μεταδεδομένα και ρυθμίσεις ορατότητας για πολλά άλμπουμ ταυτόχρονα.',
    'warning' => 'Οι αλλαγές εφαρμόζονται αμέσως και δεν μπορούν να αναιρεθούν. Τα άλμπουμ ετικετών δεν εμφανίζονται.',

    // Table columns
    'col_title' => 'Τίτλος',
    'col_owner' => 'Κάτοχος',
    'col_license' => 'Άδεια',
    'col_is_nsfw' => 'Ευαίσθητο',
    'col_is_public' => 'Δημόσιο',
    'col_is_link_required' => 'Σύνδεσμος',
    'col_grants_full_photo_access' => 'Πλήρης φωτογραφία',
    'col_grants_download' => 'Λήψη',
    'col_grants_upload' => 'Μεταφόρτωση',
    'col_photo_sorting' => 'Ταξ. φωτογραφιών',
    'col_album_sorting' => 'Ταξ. άλμπουμ',
    'col_created_at' => 'Δημιουργία',

    // Filter
    'filter_placeholder' => 'Αναζήτηση με τίτλο...',

    // Pagination
    'per_page' => 'Ανά σελίδα',
    'total_selected' => ':n άλμπουμ επιλέχθηκε|:n άλμπουμ επιλέχθηκαν',
    'select_all_page' => 'Επιλογή όλων σε αυτή τη σελίδα',
    'select_all_matching' => 'Επιλογή όλων των αποτελεσμάτων',
    'cap_warning' => 'Επιλέχθηκαν μόνο τα πρώτα 1.000 άλμπουμ.',

    // Mode toggle
    'mode_paginated' => 'Με σελίδες',
    'mode_infinite' => 'Συνεχής κύλιση',

    // Action buttons
    'action_delete' => 'Διαγραφή',
    'action_set_owner' => 'Ορισμός κατόχου',
    'action_edit_fields' => 'Επεξεργασία πεδίων',

    // Edit Fields modal
    'edit_fields_title' => 'Επεξεργασία πεδίων',
    'edit_fields_description' => 'Θα ενημερωθούν μόνο τα επιλεγμένα πεδία. Οι κενές τιμές εκκαθαρίζουν το πεδίο.',
    'section_metadata' => 'Μεταδεδομένα',
    'section_visibility' => 'Ορατότητα',
    'field_description' => 'Περιγραφή',
    'field_copyright' => 'Πνευματικά δικαιώματα',
    'field_license' => 'Άδεια',
    'field_photo_layout' => 'Διάταξη φωτογραφιών',
    'field_photo_sorting_col' => 'Στήλη ταξινόμησης φωτογραφιών',
    'field_photo_sorting_order' => 'Σειρά ταξινόμησης φωτογραφιών',
    'field_album_sorting_col' => 'Στήλη ταξινόμησης άλμπουμ',
    'field_album_sorting_order' => 'Σειρά ταξινόμησης άλμπουμ',
    'field_album_thumb_aspect_ratio' => 'Αναλογία διαστάσεων μικρογραφίας',
    'field_album_timeline' => 'Χρονολόγιο άλμπουμ',
    'field_photo_timeline' => 'Χρονολόγιο φωτογραφιών',
    'field_published_at' => 'Ημερομηνία δημοσίευσης στο Flow',
    'field_is_nsfw' => 'Ευαίσθητο',
    'field_is_public' => 'Δημόσιο',
    'field_is_link_required' => 'Απαιτείται σύνδεσμος',
    'field_grants_full_photo_access' => 'Πλήρης πρόσβαση στη φωτογραφία',
    'field_grants_download' => 'Λήψη',
    'field_grants_upload' => 'Μεταφόρτωση (SE)',
    'apply' => 'Εφαρμογή',
    'cancel' => 'Ακύρωση',

    // Set Owner modal
    'set_owner_title' => 'Ορισμός κατόχου',
    'set_owner_description' => 'Όλα τα επιλεγμένα άλμπουμ θα μετακινηθούν στο ανώτατο επίπεδο και θα μεταβιβαστούν επίσης και τα υποάλμπουμ τους.',
    'set_owner_select_user' => 'Επιλέξτε νέο κάτοχο',
    'transfer' => 'Μεταβίβαση',

    // Delete confirmation modal
    'delete_title' => 'Διαγραφή άλμπουμ',
    'delete_confirm' => 'Πρόκειται να διαγράψετε οριστικά :count άλμπουμ μαζί με όλα τα υποάλμπουμ και τις φωτογραφίες του. Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.|Πρόκειται να διαγράψετε οριστικά :count άλμπουμ μαζί με όλα τα υποάλμπουμ και τις φωτογραφίες τους. Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.',
    'confirm_delete' => 'Επιβεβαίωση διαγραφής',

    // Toasts
    'success_patch' => 'Τα άλμπουμ ενημερώθηκαν επιτυχώς.',
    'success_set_owner' => 'Η κυριότητα μεταβιβάστηκε επιτυχώς.',
    'success_delete' => 'Τα άλμπουμ διαγράφηκαν επιτυχώς.',
    'error_load' => 'Αποτυχία φόρτωσης άλμπουμ.',
    'error_load_ids' => 'Αποτυχία φόρτωσης αναγνωριστικών άλμπουμ.',
    'error_patch' => 'Αποτυχία ενημέρωσης άλμπουμ.',
    'error_missing_timezone' => 'Επιλέξτε ζώνη ώρας για την ημερομηνία δημοσίευσης.',
    'error_set_owner' => 'Αποτυχία μεταβίβασης κυριότητας.',
    'error_delete' => 'Αποτυχία διαγραφής άλμπουμ.',
    'error_load_users' => 'Αποτυχία φόρτωσης χρηστών.',
];
