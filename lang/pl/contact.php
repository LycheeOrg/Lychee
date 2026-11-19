<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'Kontakt',
    'description' => 'Chętnie się z Tobą skontaktujemy!',
    'name_label' => 'Imię i nazwisko',
    'name_placeholder' => 'Twoje imię',
    'email_label' => 'E-mail lub inna forma kontaktu',
    'email_placeholder' => 'twoj@email.com',
    'message_label' => 'Wiadomość',
    'message_placeholder' => 'Wpisz tutaj swoją wiadomość...',
    'security_question_label' => 'Pytanie zabezpieczające',
    'security_answer_placeholder' => 'Twoja odpowiedź',
    'sample_qa_label' => 'Przykładowe pytanie i odpowiedź',
    'consent_label' => 'Akceptuję',
    'consent_privacy_link' => 'politykę prywatności',
    'name_required_error' => 'Imię jest wymagane.',
    'email_required_error' => 'Forma kontaktu jest wymagana.',
    'message_min_length_error' => 'Wiadomość musi mieć co najmniej 10 znaków.',
    'security_answer_required_error' => 'Odpowiedz na pytanie zabezpieczające.',
    'consent_required_error' => 'Musisz zaakceptować politykę prywatności.',
    'submit_button' => 'Wyślij wiadomość',
    'clear_button' => 'Wyczyść',
    'success_message' => 'Dziękujemy za wiadomość. Wkrótce się odezwiemy.',
    'submit_error' => 'Podczas wysyłania wiadomości wystąpił błąd. Spróbuj ponownie.',
    'rate_limit_error' => 'Zbyt wiele zgłoszeń. Spróbuj ponownie później.',
    'validation_error' => 'Popraw poniższe błędy.',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'Wiadomości kontaktowe',
        'description' => 'Wiadomości wysłane przez formularz kontaktowy.',
        'no_messages' => 'Brak wiadomości kontaktowych.',
        'name_column' => 'Imię',
        'email_column' => 'E-mail',
        'message_column' => 'Wiadomość',
        'date_column' => 'Data',
        'read_column' => 'Przeczytana',
        'search_placeholder' => 'Szukaj wiadomości...',
        'filter_unread' => 'Nieprzeczytane',
        'filter_read' => 'Przeczytane',
        'filter_all' => 'Wszystkie',
        'mark_read' => 'Oznacz jako przeczytaną',
        'mark_unread' => 'Oznacz jako nieprzeczytaną',
        'delete_confirm_header' => 'Usuń wiadomość',
        'delete_confirm_message' => 'Czy na pewno chcesz usunąć wiadomość od ":name"?',
        'delete_warning' => 'Tej operacji nie można cofnąć.',
        'delete_success' => 'Wiadomość usunięta.',
        'delete_error' => 'Nie udało się usunąć wiadomości.',
        'update_error' => 'Nie udało się zaktualizować wiadomości.',
        'cancel' => 'Anuluj',
        'delete' => 'Usuń',
    ],
];
