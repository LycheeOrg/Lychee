<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'Kapcsolat',
    'description' => 'Örömmel hallunk Öntől!',
    'name_label' => 'Név',
    'name_placeholder' => 'Az Ön neve',
    'email_label' => 'E-mail vagy elérhetőség',
    'email_placeholder' => 'az.on@email.com',
    'message_label' => 'Üzenet',
    'message_placeholder' => 'Írja ide üzenetét...',
    'security_question_label' => 'Biztonsági kérdés',
    'security_answer_placeholder' => 'Az Ön válasza',
    'sample_qa_label' => 'Minta kérdés és válasz',
    'consent_label' => 'Elfogadom az',
    'consent_privacy_link' => 'adatvédelmi irányelveket',
    'name_required_error' => 'A név megadása kötelező.',
    'email_required_error' => 'Az elérhetőség megadása kötelező.',
    'message_min_length_error' => 'Az üzenetnek legalább 10 karakterből kell állnia.',
    'security_answer_required_error' => 'Kérjük, válaszoljon a biztonsági kérdésre.',
    'consent_required_error' => 'El kell fogadnia az adatvédelmi irányelveket.',
    'submit_button' => 'Üzenet küldése',
    'clear_button' => 'Törlés',
    'success_message' => 'Köszönjük üzenetét. Hamarosan válaszolunk.',
    'submit_error' => 'Hiba történt az üzenet elküldése közben. Kérjük, próbálja újra.',
    'rate_limit_error' => 'Túl sok beküldés. Kérjük, próbálja újra később.',
    'validation_error' => 'Kérjük, javítsa az alábbi hibákat.',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'Kapcsolati üzenetek',
        'description' => 'A kapcsolatfelvételi űrlapon keresztül beküldött üzenetek.',
        'no_messages' => 'Még nincsenek kapcsolati üzenetek.',
        'name_column' => 'Név',
        'email_column' => 'E-mail',
        'message_column' => 'Üzenet',
        'date_column' => 'Dátum',
        'read_column' => 'Olvasott',
        'search_placeholder' => 'Üzenetek keresése...',
        'filter_unread' => 'Olvasatlan',
        'filter_read' => 'Olvasott',
        'filter_all' => 'Összes',
        'mark_read' => 'Megjelölés olvasottként',
        'mark_unread' => 'Megjelölés olvasatlanként',
        'delete_confirm_header' => 'Üzenet törlése',
        'delete_confirm_message' => 'Biztosan törölni szeretné ":name" üzenetét?',
        'delete_warning' => 'Ez a művelet nem vonható vissza.',
        'delete_success' => 'Üzenet törölve.',
        'delete_error' => 'Nem sikerült törölni az üzenetet.',
        'update_error' => 'Nem sikerült frissíteni az üzenetet.',
        'cancel' => 'Mégse',
        'delete' => 'Törlés',
    ],
];
