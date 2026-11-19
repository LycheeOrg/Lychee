<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Utenti',
    'description' => 'Qui puoi gestire gli utenti della tua installazione di Lychee. Puoi creare, modificare ed eliminare utenti.',
    'create' => 'Crea un nuovo utente',
    'username' => 'Nome utente',
    'password' => 'Password',
    'legend' => 'Legenda',
    'upload_rights' => 'Se selezionato, l\'utente può caricare contenuti.',
    'edit_rights' => 'Se selezionato, l\'utente può modificare il proprio profilo (nome utente, password).',
    'upload_trust_level' => 'Livello di fiducia per il caricamento — determina se i caricamenti sono subito pubblici.',

    'quota' => 'Se impostato, l\'utente ha una quota di spazio per le immagini (in kB).',
    'user_deleted' => 'Utente eliminato',
    'user_created' => 'Utente creato',
    'user_updated' => 'Utente aggiornato',
    'change_saved' => 'Modifica salvata!',
    'create_edit' => [
        'upload_rights' => 'L\'utente può caricare contenuti.',
        'edit_rights' => 'L\'utente può modificare il proprio profilo (nome utente, password).',
        'admin_rights' => 'L\'utente ha i diritti di amministratore.',
        'upload_trust_level' => 'Livello di fiducia per il caricamento',
        'upload_trust_level_check' => 'Verifica – i caricamenti richiedono l\'approvazione di un amministratore prima di diventare pubblici.',
        'upload_trust_level_monitor' => 'Monitoraggio – i caricamenti sono pubblici a meno che il contenuto non venga segnalato.',
        'upload_trust_level_trust_but_verify' => 'Fidati ma verifica – i caricamenti sono pubblici; i rilevamenti da revisionare sono approvati automaticamente, quelli da bloccare sono configurabili.',
        'upload_trust_level_trusted' => 'Fidato – i caricamenti sono subito pubblici.',
        'trust_level_options' => [
            'trusted' => 'Fidato',
            'trust_but_verify' => 'Fidati ma verifica',
            'monitor' => 'Monitoraggio',
            'check' => 'Verifica',
        ],
        'quota' => 'L\'utente ha un limite di quota.',
        'quota_kb' => 'quota in kB (0 per il valore predefinito)',
        'note' => 'Nota amministratore (non visibile pubblicamente)',
        'create' => 'Crea',
        'edit' => 'Modifica',
    ],
    'invite' => [
        'button' => 'Invita utente',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Questo link è valido per %d giorni.',
    ],
    'line' => [
        'owner' => 'Proprietario',
        'admin' => 'Utente amministratore',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
    ],
];
