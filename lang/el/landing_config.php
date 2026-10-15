<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page configuration (admin page shell)
    |--------------------------------------------------------------------------
    */

    'title' => 'Αρχική σελίδα',
    'tab_settings' => 'Ρυθμίσεις',
    'tab_links' => 'Σύνδεσμοι',
    'tab_featured' => 'Προβεβλημένα',

    'section_layout' => 'Διάταξη & δομή',
    'section_hero' => 'Hero',
    'section_background_landscape' => 'Φόντο (οριζόντιο)',
    'section_background_portrait' => 'Φόντο (κατακόρυφο)',
    'section_cta_position' => 'Θέση κουμπιού CTA',
    'section_login_position' => 'Πλαίσιο σύνδεσης',
    'section_meridian' => 'Γραμμές Meridian',
    'section_content' => 'Περιεχόμενο',

    'field_layout' => 'Διάταξη',
    'field_intro_screen_enabled' => 'Εισαγωγική οθόνη',
    'field_hero_text_position' => 'Θέση κειμένου hero',
    'field_hero_text_color' => 'Χρώμα κειμένου hero',
    'field_hero_text_opacity' => 'Αδιαφάνεια κειμένου hero',
    'field_animation_preset' => 'Προεπιλογή κίνησης',
    'field_backdrop_opacity' => 'Αδιαφάνεια επικάλυψης φόντου (:value%)',
    'field_cta_text' => 'κείμενο',
    'field_cta_text_placeholder' => 'Αφήστε κενό για την προεπιλογή της διάταξης',
    'field_about_enabled' => 'Ενότητα «Σχετικά»',
    'field_about_text' => 'Κείμενο «Σχετικά»',

    'field_background_mode' => 'Πηγή',
    'background_mode_options' => [
        'static' => 'URL',
        'photo_id' => 'ID φωτογραφίας',
        'random' => 'Τυχαία δημόσια φωτογραφία',
        'latest_album_cover' => 'Εξώφυλλο πιο πρόσφατου άλμπουμ',
        'random_from_album' => 'Τυχαία φωτογραφία από άλμπουμ',
    ],
    'field_background_url' => 'URL εικόνας',
    'field_background_url_placeholder' => 'https://…',
    'field_background_photo_id' => 'ID φωτογραφίας',
    'field_background_photo_id_placeholder' => 'ID φωτογραφίας 24 χαρακτήρων',
    'field_background_photo_id_hint' => 'Το ID της φωτογραφίας που θα χρησιμοποιηθεί. Ανοίξτε μια φωτογραφία και αντιγράψτε τους τελευταίους 24 χαρακτήρες από το URL.',
    'field_background_album_id' => 'ID άλμπουμ',
    'field_background_album_id_placeholder' => 'ID άλμπουμ 24 χαρακτήρων',
    'background_load_error' => 'Δεν ήταν δυνατή η φόρτωση της φωτογραφίας. Βεβαιωθείτε ότι το ID είναι σωστό και ότι έχετε πρόσβαση σε αυτή.',
    'background_mode_hint' => [
        'random' => 'Σε κάθε φόρτωση σελίδας επιλέγεται μια τυχαία δημόσια φωτογραφία.',
        'latest_album_cover' => 'Χρησιμοποιείται το εξώφυλλο του πιο πρόσφατα δημοσιευμένου δημόσιου άλμπουμ.',
        'random_from_album' => 'Σε κάθε φόρτωση σελίδας επιλέγεται μια τυχαία φωτογραφία από αυτό το άλμπουμ. Η προεπισκόπηση ενημερώνεται μόνο μετά την αποθήκευση.',
    ],

    'preview_orientation_landscape' => 'Προεπισκόπηση οριζόντιου φόντου',
    'preview_orientation_portrait' => 'Προεπισκόπηση κατακόρυφου φόντου',

    'field_cta_position' => 'Θέση',
    'cta_position_options' => [
        'top-left' => 'Πάνω αριστερά',
        'top' => 'Πάνω κέντρο',
        'top-right' => 'Πάνω δεξιά',
        'left' => 'Μέση αριστερά',
        'center' => 'Κέντρο',
        'right' => 'Μέση δεξιά',
        'bottom-left' => 'Κάτω αριστερά',
        'bottom' => 'Κάτω κέντρο',
        'bottom-right' => 'Κάτω δεξιά',
    ],
    'field_cta_shift_type' => 'Μονάδα μετατόπισης',
    'cta_shift_type_options' => [
        'relative' => 'Σχετική (%)',
        'absolute' => 'Απόλυτη (px)',
    ],
    'cta_shift_type_hint' => 'Οι σχετικές μετατοπίσεις είναι ποσοστό του μεγέθους της οθόνης· οι απόλυτες μετατοπίσεις είναι σταθερός αριθμός pixel.',
    'field_cta_shift_x' => 'Οριζόντια μετατόπιση (:value)',
    'cta_shift_x_direction_options' => [
        'left' => 'Αριστερά',
        'right' => 'Δεξιά',
    ],
    'field_cta_shift_y' => 'Κατακόρυφη μετατόπιση (:value)',
    'cta_shift_y_direction_options' => [
        'up' => 'Πάνω',
        'down' => 'Κάτω',
    ],
    'reset_to_zero' => 'Επαναφορά στο 0',

    'field_login_position' => 'Θέση',
    'login_position_options' => [
        'side' => 'Πλάι',
        'center' => 'Κέντρο',
    ],
    'field_login_position_hint' => 'Σε στενές οθόνες το πλαίσιο σύνδεσης είναι πάντα κεντραρισμένο κάτω από το hero, ανεξάρτητα από αυτή τη ρύθμιση. Εμφανίζεται ολισθαίνοντας από την πλευρά της κατεύθυνσης ανάγνωσης όταν η προεπιλογή κίνησης είναι "Slide reveal" ή "Parallax scroll".',

    'field_meridian_explore_offset' => 'Ύψος ετικέτας εξερεύνησης (:value%)',
    'field_meridian_contact_offset' => 'Ύψος ετικέτας επικοινωνίας (:value%)',
    'field_meridian_offset_hint' => 'Πόσο χαμηλά σε κάθε γραμμή πλήρους ύψους βρίσκεται η ετικέτα της: 0% είναι η κορυφή, 100% είναι το κάτω μέρος.',
    'field_meridian_explore_line_position' => 'Θέση γραμμής εξερεύνησης (:value%)',
    'field_meridian_contact_line_position' => 'Θέση γραμμής επικοινωνίας (:value%)',
    'field_meridian_line_position_hint' => 'Πόσο μακριά από την αριστερή άκρη βρίσκεται κάθε γραμμή: 0% είναι η αριστερή άκρη, 100% η δεξιά άκρη. Η γραμμή εξερεύνησης κεντράρεται πάντα όταν η γραμμή επικοινωνίας είναι απενεργοποιημένη.',

    'preview_title' => 'Ζωντανή προεπισκόπηση',
    'preview_hint' => 'Ενημερώνεται αμέσως καθώς επεξεργάζεστε — τίποτα δεν αποθηκεύεται μέχρι να πατήσετε Αποθήκευση.',
    'flat_list_hint' => 'Αυτές οι ρυθμίσεις μπορούν επίσης να τροποποιηθούν από τη γενική λίστα Ρυθμίσεων.',

    'save' => 'Αποθήκευση',
    'saved' => 'Οι ρυθμίσεις της αρχικής σελίδας αποθηκεύτηκαν.',
    'save_error' => 'Αποτυχία αποθήκευσης των ρυθμίσεων της αρχικής σελίδας.',

    'se_required' => 'Αυτή η διάταξη/προεπιλογή απαιτεί Lychee SE.',
];
