<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Regler för namnbyte',

    // Modal titles
    'create_rule' => 'Skapa namnbytesregel',
    'edit_rule' => 'Redigera namnbytesregel',

    // Form fields
    'rule_name' => 'Regelnamn',
    'description' => 'Beskrivning',
    'pattern' => 'Mönster',
    'replacement' => 'Ersättning',
    'mode' => 'Läge',
    'order' => 'Ordning',
    'enabled' => 'Aktiverad',
    'photo_rule' => 'Regel för foton',
    'album_rule' => 'Regel för album',

    // Form placeholders and help text
    'description_placeholder' => 'Valfri beskrivning av vad regeln gör',
    'pattern_help' => 'Mönster att matcha (t.ex. IMG_, DSC_)',
    'replacement_help' => 'Ersättningstext (t.ex. Photo_, Camera_)',
    'order_help' => 'Lägre nummer behandlas först (1 = högsta prioritet)',
    'enabled_help' => '(Endast aktiverade regler tillämpas vid namnbyte)',

    // Mode options
    'mode_first' => 'Första förekomsten',
    'mode_all' => 'Alla förekomster',
    'mode_regex' => 'Reguljärt uttryck',
    'mode_trim' => 'Ta bort blanksteg',
    'mode_strtolower' => 'gemener',
    'mode_strtoupper' => 'VERSALER',
    'mode_ucwords' => 'Versal I Varje Ord',
    'mode_ucfirst' => 'Versal första bokstaven',

    'mode_first_description' => 'Ersätt endast första förekomsten',
    'mode_all_description' => 'Ersätt alla förekomster',
    'mode_regex_description' => 'Använd matchning med reguljära uttryck',
    'mode_trim_description' => 'Ta bort inledande och avslutande blanksteg',
    'mode_strtolower_description' => 'Konvertera texten till gemener',
    'mode_strtoupper_description' => 'Konvertera texten till VERSALER',
    'mode_ucwords_description' => 'Inled varje ord med versal',
    'mode_ucfirst_description' => 'Gör endast första bokstaven till versal',

    'regex_help' => 'Använd reguljära uttryck för att matcha mönster. Om du till exempel vill ersätta <code>IMG_1234.jpeg</code> med <code>1234_JPG.jpeg</code> kan du använda <code>/IMG_(\\d+)/</code> som sökmönster och <code>$1_JPG</code> som ersättning. Fler förklaringar och exempel finns i följande länkar.',

    // Buttons
    'cancel' => 'Avbryt',
    'create' => 'Skapa',
    'update' => 'Uppdatera',
    'create_first_rule' => 'Skapa din första regel',

    // Validation messages
    'rule_name_required' => 'Regelnamn krävs',
    'pattern_required' => 'Mönster krävs',
    'replacement_required' => 'Ersättning krävs',
    'mode_required' => 'Läge krävs',
    'order_positive' => 'Ordningen måste vara ett positivt tal',

    // Success messages
    'rule_created' => 'Namnbytesregeln har skapats',
    'rule_updated' => 'Namnbytesregeln har uppdaterats',
    'rule_deleted' => 'Namnbytesregeln har raderats',

    // Error messages
    'failed_to_create' => 'Det gick inte att skapa namnbytesregeln',
    'failed_to_update' => 'Det gick inte att uppdatera namnbytesregeln',
    'failed_to_delete' => 'Det gick inte att radera namnbytesregeln',
    'failed_to_load' => 'Det gick inte att läsa in namnbytesreglerna',

    // List view
    'rules_count' => ':count regler',
    'no_rules' => 'Inga namnbytesregler hittades',
    'loading' => 'Läser in namnbytesregler...',
    'pattern_label' => 'Mönster',
    'replace_with_label' => 'Ersätt med',
    'photo' => 'Foto',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Bekräfta radering',
    'confirm_delete_message' => 'Är du säker på att du vill radera regeln ":rule"?',
    'delete' => 'Radera',

    // Status messages
    'success' => 'Klart',
    'error' => 'Fel',

    // Placeholders
    'select_mode' => 'Välj läge för namnbyte',
    'execution_order' => 'Körordning',

    // Test functionality
    'test_input_placeholder' => 'Ange ett filnamn för att testa dina namnbytesregler (t.ex. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Resultat',
    'test_failed' => 'Det gick inte att testa namnbytesreglerna',
    'apply_photo_rules' => 'Tillämpa fotoregler',
    'apply_album_rules' => 'Tillämpa albumregler',
];
