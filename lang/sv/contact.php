<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'Kontakta oss',
    'description' => 'Vi vill gärna höra från dig!',
    'name_label' => 'Namn',
    'name_placeholder' => 'Ditt namn',
    'email_label' => 'E-post eller kontaktsätt',
    'email_placeholder' => 'din@epost.se',
    'message_label' => 'Meddelande',
    'message_placeholder' => 'Skriv ditt meddelande här...',
    'security_question_label' => 'Säkerhetsfråga',
    'security_answer_placeholder' => 'Ditt svar',
    'sample_qa_label' => 'Exempel på fråga och svar',
    'consent_label' => 'Jag godkänner',
    'consent_privacy_link' => 'integritetspolicyn',
    'name_required_error' => 'Namn krävs.',
    'email_required_error' => 'Kontaktsätt krävs.',
    'message_min_length_error' => 'Meddelandet måste vara minst 10 tecken.',
    'security_answer_required_error' => 'Besvara säkerhetsfrågan.',
    'consent_required_error' => 'Du måste godkänna integritetspolicyn.',
    'submit_button' => 'Skicka meddelande',
    'clear_button' => 'Rensa',
    'success_message' => 'Tack för ditt meddelande. Vi återkommer snart.',
    'submit_error' => 'Ett fel uppstod när ditt meddelande skulle skickas. Försök igen.',
    'rate_limit_error' => 'För många inskick. Försök igen senare.',
    'validation_error' => 'Korrigera felen nedan.',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'Kontaktmeddelanden',
        'description' => 'Meddelanden som skickats via kontaktformuläret.',
        'no_messages' => 'Inga kontaktmeddelanden än.',
        'name_column' => 'Namn',
        'email_column' => 'E-post',
        'message_column' => 'Meddelande',
        'date_column' => 'Datum',
        'read_column' => 'Läst',
        'search_placeholder' => 'Sök meddelanden...',
        'filter_unread' => 'Olästa',
        'filter_read' => 'Lästa',
        'filter_all' => 'Alla',
        'mark_read' => 'Markera som läst',
        'mark_unread' => 'Markera som oläst',
        'delete_confirm_header' => 'Radera meddelande',
        'delete_confirm_message' => 'Är du säker på att du vill radera meddelandet från ":name"?',
        'delete_warning' => 'Åtgärden kan inte ångras.',
        'delete_success' => 'Meddelandet har raderats.',
        'delete_error' => 'Det gick inte att radera meddelandet.',
        'update_error' => 'Det gick inte att uppdatera meddelandet.',
        'cancel' => 'Avbryt',
        'delete' => 'Radera',
    ],
];
