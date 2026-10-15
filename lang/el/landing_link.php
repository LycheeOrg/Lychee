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

    'title' => 'Επιπλέον σύνδεσμοι',
    'description' => 'Διαχειριστείτε μια ελεύθερη, ταξινομημένη λίστα επιπλέον συνδέσμων που εμφανίζονται στο μενού πλοήγησης ή/και στο υποσέλιδο της αρχικής σελίδας.',

    // Empty state
    'no_links' => 'Δεν έχουν ρυθμιστεί ακόμη επιπλέον σύνδεσμοι.',
    'create_first' => 'Δημιουργήστε τον πρώτο σας σύνδεσμο',

    // Table columns
    'col_label' => 'Ετικέτα',
    'col_url' => 'URL',
    'col_placement' => 'Θέση',
    'col_enabled' => 'Ενεργό',
    'col_actions' => 'Ενέργειες',

    // Placement labels
    'placement_nav' => 'Πλοήγηση',
    'placement_footer' => 'Υποσέλιδο',
    'placement_both' => 'Πλοήγηση & υποσέλιδο',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => 'Ενσωματωμένος',
    'built_in_cannot_delete' => 'Αυτός ο ενσωματωμένος σύνδεσμος δεν μπορεί να διαγραφεί. Απενεργοποιήστε τον ή αλλάξτε τη σειρά του όπως με κάθε άλλο σύνδεσμο.',
    'built_in_url_hint' => 'Αυτός είναι ενσωματωμένος σύνδεσμος και ο προορισμός του δεν μπορεί να αλλάξει.',

    // Buttons
    'create' => 'Δημιουργία συνδέσμου',
    'edit' => 'Επεξεργασία',
    'delete' => 'Διαγραφή',
    'cancel' => 'Ακύρωση',
    'save' => 'Αποθήκευση',

    // Form fields
    'field_label' => 'Ετικέτα',
    'field_label_placeholder' => 'π.χ. Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => 'Θέση',
    'field_open_in_new_tab' => 'Άνοιγμα σε νέα καρτέλα',
    'field_enabled' => 'Ενεργό',

    // Modal titles
    'modal_create_title' => 'Δημιουργία συνδέσμου',
    'modal_edit_title' => 'Επεξεργασία συνδέσμου',

    // Delete confirmation
    'confirm_delete_header' => 'Διαγραφή συνδέσμου',
    'confirm_delete_message' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε τον σύνδεσμο ":label"; Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.',

    // Toasts
    'created' => 'Ο σύνδεσμος δημιουργήθηκε επιτυχώς.',
    'updated' => 'Ο σύνδεσμος ενημερώθηκε επιτυχώς.',
    'deleted' => 'Ο σύνδεσμος διαγράφηκε επιτυχώς.',
    'reordered' => 'Η σειρά των συνδέσμων άλλαξε επιτυχώς.',
    'error_load' => 'Αποτυχία φόρτωσης των συνδέσμων.',
    'error_save' => 'Αποτυχία αποθήκευσης του συνδέσμου.',
    'error_delete' => 'Αποτυχία διαγραφής του συνδέσμου.',
    'error_reorder' => 'Αποτυχία αλλαγής σειράς των συνδέσμων.',
];
