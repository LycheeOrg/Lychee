<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contact form page
    |--------------------------------------------------------------------------
    */
    'title' => 'Kontakt',
    'description' => 'Rádi si přečteme vaši zprávu!',
    'name_label' => 'Jméno',
    'name_placeholder' => 'Vaše jméno',
    'email_label' => 'Email nebo jiný kontakt',
    'email_placeholder' => 'vas@email.cz',
    'message_label' => 'Zpráva',
    'message_placeholder' => 'Vaše zpráva zde...',
    'security_question_label' => 'Bezpečnostní otázka',
    'security_answer_placeholder' => 'Odpověď',
    'sample_qa_label' => 'Vzorové Q&A',
    'consent_label' => 'Souhlasím s',
    'consent_privacy_link' => 'záqsady ochrany osobních údajů',
    'name_required_error' => 'Vyplňte jméno.',
    'email_required_error' => 'Email nebo jiný kontakt musí být vyplněn.',
    'message_min_length_error' => 'Zpráva musí obsahovat alespoň 10 znaků.',
    'security_answer_required_error' => 'Prosím, odpovězte na bezpečnostní otázku.',
    'consent_required_error' => 'Musíte souhlasit se zásadami ochrany osobních údajů.',
    'submit_button' => 'Odeslat zprávu',
    'clear_button' => 'Vyčistit',
    'success_message' => 'Děkujeme za vaši zprávu. Brzy vám odpovíme.',
    'submit_error' => 'Pri odesílání zprávy se vyskytla chyba. Zkuste to prosím později.',
    'rate_limit_error' => 'Příliš mnoho odeslání, zkuste to znovu později.',
    'validation_error' => 'Prosím, opravte chyby výše.',

    /*
    |--------------------------------------------------------------------------
    | Admin contact messages page
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'title' => 'Zprávy z kontaktního formuláře',
        'description' => 'Zprávy odeslané prostřednictvím kontaktního formuláře.',
        'no_messages' => 'Zatím žádné zprávy.',
        'name_column' => 'Jméno',
        'email_column' => 'E-mail',
        'message_column' => 'Zpráva',
        'date_column' => 'Datum',
        'read_column' => 'Přečteno',
        'search_placeholder' => 'Hledat zprávy...',
        'filter_unread' => 'Nepřečtené',
        'filter_read' => 'Přečtené',
        'filter_all' => 'Vše',
        'mark_read' => 'Označit jako přečtené',
        'mark_unread' => 'Označit jako nepřečtené',
        'delete_confirm_header' => 'Smazat zprávu',
        'delete_confirm_message' => 'Opravdu chcete smazat zprávu od ":name"?',
        'delete_warning' => 'Tuto akci nelze vrátit zpět.',
        'delete_success' => 'Zpráva byla smazána.',
        'delete_error' => 'Zprávu se nepodařilo smazat.',
        'update_error' => 'Zprávu se nepodařilo aktualizovat.',
        'cancel' => 'Zrušit',
        'delete' => 'Smazat',
    ],
];
