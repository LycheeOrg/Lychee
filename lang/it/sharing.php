<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Condivisione',
    'info' => 'Questa pagina offre una panoramica dei permessi di condivisione associati agli album e permette di modificarli.',
    'album_title' => 'Titolo album',
    'username' => 'Nome utente',
    'no_data' => 'L\'elenco delle condivisioni è vuoto.',
    'filter_placeholder' => 'Cerca per titolo album...',
    'hide_empty' => 'Nascondi album senza condivisioni',
    'screen_too_small' => 'Questa pagina richiede uno schermo più grande.',
    'share' => 'Condividi',
    'add_new_access_permission' => 'Aggiungi un nuovo permesso di accesso',
    'permission_deleted' => 'Permesso eliminato!',
    'permission_created' => 'Permesso creato!',
    'propagate' => 'Propaga',
    'propagate_help' => 'Propaga i permessi di accesso attuali a tutti i discendenti<br>(sotto-album e i rispettivi sotto-album ecc.)',
    'propagate_default' => 'Per impostazione predefinita, i permessi esistenti (album-utente)<br>vengono aggiornati e quelli mancanti aggiunti.<br>I permessi aggiuntivi non presenti in questo elenco restano invariati.',
    'propagate_overwrite' => 'Sovrascrivi i permessi esistenti invece di aggiornarli.<br>Verranno rimossi anche tutti i permessi non presenti in questo elenco.',
    'propagate_warning' => 'Questa azione non può essere annullata.',
    'permission_overwritten' => 'Propagazione riuscita! Permessi sovrascritti!',
    'permission_updated' => 'Propagazione riuscita! Permessi aggiornati!',
    'bluk_share' => 'Condivisione multipla',
    'bulk_share_instr' => 'Seleziona più album e utenti con cui condividere.',
    'albums' => 'Album',
    'users' => 'Utenti',
    'no_users' => 'Nessun utente selezionabile.',
    'no_albums' => 'Nessun album selezionabile.',
    'grants' => [
        'read' => 'Concede l\'accesso in lettura',
        'original' => 'Concede l\'accesso alla foto originale',
        'download' => 'Concede il download',
        'upload' => 'Concede il caricamento',
        'edit' => 'Concede la modifica',
        'move' => 'Concede lo spostamento, la copia e l\'unione',
        'delete' => 'Concede l\'eliminazione',
    ],
];
