<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Κανόνες μετονομασίας',

    // Modal titles
    'create_rule' => 'Δημιουργία κανόνα μετονομασίας',
    'edit_rule' => 'Επεξεργασία κανόνα μετονομασίας',

    // Form fields
    'rule_name' => 'Όνομα κανόνα',
    'description' => 'Περιγραφή',
    'pattern' => 'Μοτίβο',
    'replacement' => 'Αντικατάσταση',
    'mode' => 'Λειτουργία',
    'order' => 'Σειρά',
    'enabled' => 'Ενεργός',
    'photo_rule' => 'Κανόνας για φωτογραφίες',
    'album_rule' => 'Κανόνας για άλμπουμ',

    // Form placeholders and help text
    'description_placeholder' => 'Προαιρετική περιγραφή του τι κάνει αυτός ο κανόνας',
    'pattern_help' => 'Μοτίβο προς αντιστοίχιση (π.χ. IMG_, DSC_)',
    'replacement_help' => 'Κείμενο αντικατάστασης (π.χ. Photo_, Camera_)',
    'order_help' => 'Οι μικρότεροι αριθμοί εκτελούνται πρώτοι (1 = υψηλότερη προτεραιότητα)',
    'enabled_help' => '(Μόνο οι ενεργοί κανόνες εφαρμόζονται κατά τη μετονομασία)',

    // Mode options
    'mode_first' => 'Πρώτη εμφάνιση',
    'mode_all' => 'Όλες οι εμφανίσεις',
    'mode_regex' => 'Κανονική έκφραση',
    'mode_trim' => 'Αφαίρεση κενών',
    'mode_strtolower' => 'πεζά',
    'mode_strtoupper' => 'ΚΕΦΑΛΑΙΑ',
    'mode_ucwords' => 'Κεφαλαίο Σε Κάθε Λέξη',
    'mode_ucfirst' => 'Κεφαλαίο πρώτο γράμμα',

    'mode_first_description' => 'Αντικατάσταση μόνο της πρώτης εμφάνισης',
    'mode_all_description' => 'Αντικατάσταση όλων των εμφανίσεων',
    'mode_regex_description' => 'Χρήση αντιστοίχισης με κανονική έκφραση (regex)',
    'mode_trim_description' => 'Αφαίρεση κενών στην αρχή και στο τέλος',
    'mode_strtolower_description' => 'Μετατροπή του κειμένου σε πεζά',
    'mode_strtoupper_description' => 'Μετατροπή του κειμένου σε ΚΕΦΑΛΑΙΑ',
    'mode_ucwords_description' => 'Κεφαλαίο το πρώτο γράμμα κάθε λέξης',
    'mode_ucfirst_description' => 'Κεφαλαίο μόνο το πρώτο γράμμα',

    'regex_help' => 'Χρησιμοποιήστε κανονικές εκφράσεις για την αντιστοίχιση μοτίβων. Για παράδειγμα, για να αντικαταστήσετε το <code>IMG_1234.jpeg</code> με <code>1234_JPG.jpeg</code>, μπορείτε να χρησιμοποιήσετε το <code>/IMG_(\\d+)/</code> ως μοτίβο αναζήτησης και το <code>$1_JPG</code> ως αντικατάσταση. Περισσότερες εξηγήσεις και παραδείγματα θα βρείτε στους παρακάτω συνδέσμους.',

    // Buttons
    'cancel' => 'Ακύρωση',
    'create' => 'Δημιουργία',
    'update' => 'Ενημέρωση',
    'create_first_rule' => 'Δημιουργήστε τον πρώτο σας κανόνα',

    // Validation messages
    'rule_name_required' => 'Το όνομα κανόνα είναι υποχρεωτικό',
    'pattern_required' => 'Το μοτίβο είναι υποχρεωτικό',
    'replacement_required' => 'Η αντικατάσταση είναι υποχρεωτική',
    'mode_required' => 'Η λειτουργία είναι υποχρεωτική',
    'order_positive' => 'Η σειρά πρέπει να είναι θετικός αριθμός',

    // Success messages
    'rule_created' => 'Ο κανόνας μετονομασίας δημιουργήθηκε επιτυχώς',
    'rule_updated' => 'Ο κανόνας μετονομασίας ενημερώθηκε επιτυχώς',
    'rule_deleted' => 'Ο κανόνας μετονομασίας διαγράφηκε επιτυχώς',

    // Error messages
    'failed_to_create' => 'Αποτυχία δημιουργίας κανόνα μετονομασίας',
    'failed_to_update' => 'Αποτυχία ενημέρωσης κανόνα μετονομασίας',
    'failed_to_delete' => 'Αποτυχία διαγραφής κανόνα μετονομασίας',
    'failed_to_load' => 'Αποτυχία φόρτωσης κανόνων μετονομασίας',

    // List view
    'rules_count' => ':count κανόνες',
    'no_rules' => 'Δεν βρέθηκαν κανόνες μετονομασίας',
    'loading' => 'Φόρτωση κανόνων μετονομασίας...',
    'pattern_label' => 'Μοτίβο',
    'replace_with_label' => 'Αντικατάσταση με',
    'photo' => 'Φωτογραφία',
    'album' => 'Άλμπουμ',

    // Delete confirmation
    'confirm_delete_header' => 'Επιβεβαίωση διαγραφής',
    'confirm_delete_message' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε τον κανόνα ":rule";',
    'delete' => 'Διαγραφή',

    // Status messages
    'success' => 'Επιτυχία',
    'error' => 'Σφάλμα',

    // Placeholders
    'select_mode' => 'Επιλέξτε λειτουργία μετονομασίας',
    'execution_order' => 'Σειρά εκτέλεσης',

    // Test functionality
    'test_input_placeholder' => 'Εισαγάγετε ένα όνομα αρχείου για να δοκιμάσετε τους κανόνες μετονομασίας (π.χ. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Αποτέλεσμα',
    'test_failed' => 'Αποτυχία δοκιμής κανόνων μετονομασίας',
    'apply_photo_rules' => 'Εφαρμογή κανόνων φωτογραφιών',
    'apply_album_rules' => 'Εφαρμογή κανόνων άλμπουμ',
];
