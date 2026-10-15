<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Manutenzione',
    'description' => 'In questa pagina trovi tutte le azioni necessarie per mantenere la tua installazione di Lychee in funzione in modo fluido e corretto.',
    'cleaning' => [
        'title' => 'Pulizia di %s',
        'result' => '%s eliminato.',
        'description' => 'Rimuovi tutti i contenuti da <span class="font-mono">%s</span>',
        'button' => 'Pulisci',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicati',
        'description' => 'Questo modulo conta i potenziali duplicati tra le immagini.',
        'duplicates-all' => 'Duplicati in tutti gli album',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplicati per album',
        'show' => 'Mostra duplicati',
        'load' => 'Carica conteggi',
    ],
    'fix-jobs' => [
        'title' => 'Correzione cronologia job',
        'description' => 'Contrassegna i job con stato <span class="text-ready-400">%s</span> o <span class="text-primary-500">%s</span> come <span class="text-danger-700">%s</span>.',
        'button' => 'Correggi cronologia job',
    ],
    'gen-sizevariants' => [
        'title' => '%s mancanti',
        'description' => 'Trovati %d %s che potrebbero essere generati.',
        'button' => 'Genera!',
        'success' => 'Generati con successo %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Dimensioni file mancanti',
        'description' => 'Trovate %d varianti piccole senza dimensione del file.',
        'button' => 'Recupera dati!',
        'success' => 'Calcolate con successo le dimensioni di %d varianti piccole.',
    ],
    'fix-tree' => [
        'title' => 'Statistiche dell\'albero',
        'Oddness' => 'Anomalie',
        'Duplicates' => 'Duplicati',
        'Wrong parents' => 'Genitori errati',
        'Missing parents' => 'Genitori mancanti',
        'button' => 'Correggi albero',
    ],
    'optimize' => [
        'title' => 'Ottimizza database',
        'description' => 'Se noti rallentamenti nella tua installazione, potrebbe essere perché il database non dispone di tutti gli indici necessari.',
        'button' => 'Ottimizza database',
    ],
    'missing-palettes' => [
        'title' => 'Palette mancanti',
        'description' => 'Trovate %d palette mancanti.',
        'button' => 'Crea mancanti',
    ],
    'statistics-check' => [
        'title' => 'Controllo integrità statistiche',
        'missing_photos' => '%d statistiche delle foto mancanti.',
        'missing_albums' => '%d statistiche degli album mancanti.',
        'button' => 'Crea mancanti',
    ],
    'flush-cache' => [
        'title' => 'Svuota cache',
        'description' => 'Svuota la cache di tutti gli utenti per risolvere problemi di invalidazione.',
        'button' => 'Svuota',
    ],
    'old-orders' => [
        'title' => 'Ordini vecchi',
        'description' => 'Trovati %d ordini vecchi.<br/><br/>Un ordine è considerato vecchio se ha più di 14 giorni, non ha alcun utente associato ed è ancora in attesa di pagamento oppure non contiene articoli.',
        'button' => 'Elimina ordini vecchi',
    ],
    'fulfill-orders' => [
        'title' => 'Ordini da evadere',
        'description' => 'Trovati %d ordini con contenuti non ancora resi disponibili.<br/><br/>Fai clic sul pulsante per assegnare i contenuti quando possibile.',
        'button' => 'Evadi ordini',
    ],
    'fulfill-precompute' => [
        'title' => 'Campi precalcolati degli album',
        'description' => 'Trovati %d album con campi precalcolati mancanti.<br/><br/>Equivale a eseguire: php artisan lychee:recompute-album-fields',
        'button' => 'Calcola campi',
    ],
    'flush-queue' => [
        'title' => 'Svuota coda',
        'description' => 'Trovati %d job in attesa nella coda.<br/><br/>ATTENZIONE: svuotare la coda eliminerà definitivamente tutti i job in attesa. L\'operazione non può essere annullata.',
        'button' => 'Svuota coda',
    ],
    'backfill-album-sizes' => [
        'title' => 'Statistiche dimensioni album',
        'description' => 'Trovati %d album senza statistiche sulle dimensioni.<br/><br/>Equivale a eseguire: php artisan lychee:recompute-album-sizes',
        'button' => 'Calcola dimensioni',
    ],

    'face_quality' => [
        'title' => 'Revisione qualità volti',
        'description' => 'Rivedi i volti rilevati in base al punteggio di qualità e scarta quelli di bassa qualità o errati.',
        'sort_by' => 'Ordina per:',
        'sort_confidence' => 'Affidabilità',
        'sort_blur' => 'Sfocatura (Laplaciano)',
        'no_faces' => 'Nessun volto corrispondente. Va tutto bene!',
        'col_face' => 'Volto',
        'col_person' => 'Persona',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Affidabilità',
        'col_blur' => 'Punteggio sfocatura',
        'col_actions' => 'Azioni',
        'unassigned' => 'Non assegnato',
        'dismiss' => 'Scarta volto',
        'readd' => 'Riaggiungi volto',
        'load_error' => 'Impossibile caricare i volti.',
        'dismissed' => 'Volto scartato.',
        'readded' => 'Volto riaggiunto.',
        'dismiss_error' => 'Impossibile scartare il volto.',
        'readd_error' => 'Impossibile riaggiungere il volto.',
        'batch_dismiss' => 'Scarta selezionati',
        'batch_dismissed' => ':count volto/i scartato/i.',
        'batch_dismiss_error' => 'Impossibile scartare i volti selezionati.',
        'batch_reactivate' => 'Riattiva selezionati',
        'batch_reactivated' => ':count volto/i riattivato/i.',
        'batch_reactivate_error' => 'Impossibile riattivare i volti selezionati.',
        'show_dismissed' => 'Mostra scartati',
        'show_active' => 'Mostra attivi',
        'show_unassigned' => 'Solo non assegnati',
        'select_all' => 'Seleziona tutto',
        'deselect_all' => 'Deseleziona tutto',
        'selected_count' => ':count selezionati',
    ],
    'bulk-scan-faces' => [
        'description' => 'Trovate %d foto non ancora analizzate per il riconoscimento facciale.<br/><br/>Richiede che il servizio AI Vision sia in esecuzione.',
    ],
    'run-clustering' => [
        'description' => 'Avvia il clustering dei volti nel servizio AI Vision. Raggruppa i volti rilevati per somiglianza, così puoi assegnarli alle persone.',
        'success' => 'Clustering avviato con successo.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Distruggi volti scartati',
        'description' => 'Trovati %d volti scartati. Distruggerli eliminerà definitivamente i relativi ritagli e embedding.',
        'action' => 'Distruggi tutti',
        'success' => 'Volti scartati distrutti con successo.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sincronizza embedding dei volti',
        'description' => 'Rilevata una discrepanza nel numero di volti (differenza di %d). La sincronizzazione importerà in Lychee i dati più recenti dei volti dal servizio AI Vision.',
        'action' => 'Sincronizza ora',
        'success' => 'Embedding dei volti sincronizzati con successo.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Reimposta stato scansione volti',
        'description' => 'Trovate %d foto con uno stato di scansione dei volti bloccato in attesa o non riuscito. Reimpostandole potranno essere analizzate di nuovo.',
        'action' => 'Reimposta tutti',
        'success' => 'Stati di scansione dei volti reimpostati con successo.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Elimina embedding orfani dei volti',
            'description' => 'Il servizio AI Vision potrebbe conservare fino a %d embedding non più esistenti in Lychee. L\'eliminazione invierà ad AI Vision l\'elenco attuale dei volti e cancellerà definitivamente tutto ciò che rimane.',
            'action' => 'Elimina ora',
            'success' => 'Embedding orfani dei volti eliminati con successo.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Scansione NSFW in blocco',
        'description' => 'Analizza tutte le foto non ancora scansionate alla ricerca di contenuti NSFW usando il preset configurato. Richiede che il servizio di classificazione NSFW sia in esecuzione.',
        'button' => 'Scansiona tutte le non analizzate',
        'success' => 'Scansione NSFW avviata con successo.',
    ],
];
