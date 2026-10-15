<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Regole di rinomina',

    // Modal titles
    'create_rule' => 'Crea regola di rinomina',
    'edit_rule' => 'Modifica regola di rinomina',

    // Form fields
    'rule_name' => 'Nome regola',
    'description' => 'Descrizione',
    'pattern' => 'Modello',
    'replacement' => 'Sostituzione',
    'mode' => 'Modalità',
    'order' => 'Ordine',
    'enabled' => 'Abilitata',
    'photo_rule' => 'Regola applicata alle foto',
    'album_rule' => 'Regola applicata agli album',

    // Form placeholders and help text
    'description_placeholder' => 'Descrizione facoltativa di cosa fa questa regola',
    'pattern_help' => 'Modello da cercare (es. IMG_, DSC_)',
    'replacement_help' => 'Testo sostitutivo (es. Photo_, Camera_)',
    'order_help' => 'I numeri più bassi vengono elaborati per primi (1 = priorità massima)',
    'enabled_help' => '(Solo le regole abilitate verranno applicate durante la rinomina)',

    // Mode options
    'mode_first' => 'Prima occorrenza',
    'mode_all' => 'Tutte le occorrenze',
    'mode_regex' => 'Espressione regolare',
    'mode_trim' => 'Rimuovi spazi',
    'mode_strtolower' => 'minuscolo',
    'mode_strtoupper' => 'MAIUSCOLO',
    'mode_ucwords' => 'Maiuscola Per Ogni Parola',
    'mode_ucfirst' => 'Prima lettera maiuscola',

    'mode_first_description' => 'Sostituisci solo la prima occorrenza',
    'mode_all_description' => 'Sostituisci tutte le occorrenze',
    'mode_regex_description' => 'Usa la corrispondenza con espressioni regolari',
    'mode_trim_description' => 'Rimuovi gli spazi iniziali e finali',
    'mode_strtolower_description' => 'Converti il testo in minuscolo',
    'mode_strtoupper_description' => 'Converti il testo in MAIUSCOLO',
    'mode_ucwords_description' => 'Iniziale maiuscola per ogni parola',
    'mode_ucfirst_description' => 'Solo la prima lettera maiuscola',

    'regex_help' => 'Usa le espressioni regolari per cercare corrispondenze. Ad esempio, per sostituire <code>IMG_1234.jpeg</code> con <code>1234_JPG.jpeg</code>, puoi usare <code>/IMG_(\\d+)/</code> come modello e <code>$1_JPG</code> come sostituzione. Trovi altre spiegazioni ed esempi nei link seguenti.',

    // Buttons
    'cancel' => 'Annulla',
    'create' => 'Crea',
    'update' => 'Aggiorna',
    'create_first_rule' => 'Crea la tua prima regola',

    // Validation messages
    'rule_name_required' => 'Il nome della regola è obbligatorio',
    'pattern_required' => 'Il modello è obbligatorio',
    'replacement_required' => 'La sostituzione è obbligatoria',
    'mode_required' => 'La modalità è obbligatoria',
    'order_positive' => 'L\'ordine deve essere un numero positivo',

    // Success messages
    'rule_created' => 'Regola di rinomina creata',
    'rule_updated' => 'Regola di rinomina aggiornata',
    'rule_deleted' => 'Regola di rinomina eliminata',

    // Error messages
    'failed_to_create' => 'Impossibile creare la regola di rinomina',
    'failed_to_update' => 'Impossibile aggiornare la regola di rinomina',
    'failed_to_delete' => 'Impossibile eliminare la regola di rinomina',
    'failed_to_load' => 'Impossibile caricare le regole di rinomina',

    // List view
    'rules_count' => ':count regole',
    'no_rules' => 'Nessuna regola di rinomina trovata',
    'loading' => 'Caricamento delle regole di rinomina...',
    'pattern_label' => 'Modello',
    'replace_with_label' => 'Sostituisci con',
    'photo' => 'Foto',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Conferma eliminazione',
    'confirm_delete_message' => 'Vuoi davvero eliminare la regola ":rule"?',
    'delete' => 'Elimina',

    // Status messages
    'success' => 'Operazione riuscita',
    'error' => 'Errore',

    // Placeholders
    'select_mode' => 'Seleziona la modalità di rinomina',
    'execution_order' => 'Ordine di esecuzione',

    // Test functionality
    'test_input_placeholder' => 'Inserisci un nome file per testare le regole di rinomina (es. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Risultato',
    'test_failed' => 'Impossibile testare le regole di rinomina',
    'apply_photo_rules' => 'Applica regole foto',
    'apply_album_rules' => 'Applica regole album',
];
