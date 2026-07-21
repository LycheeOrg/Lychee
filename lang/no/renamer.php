<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Omdøpingsregler',

    // Modal titles
    'create_rule' => 'Opprett omdøpingsregel',
    'edit_rule' => 'Rediger omdøpingsregel',

    // Form fields
    'rule_name' => 'Regelnavn',
    'description' => 'Beskrivelse',
    'pattern' => 'Mønster',
    'replacement' => 'Erstatning',
    'mode' => 'Modus',
    'order' => 'Rekkefølge',
    'enabled' => 'Aktivert',
    'photo_rule' => 'Regel brukt på bilder',
    'album_rule' => 'Regel brukt på album',

    // Form placeholders and help text
    'description_placeholder' => 'Valgfri beskrivelse av hva denne regelen gjør',
    'pattern_help' => 'Mønster som skal samsvare (f.eks. IMG_, DSC_)',
    'replacement_help' => 'Erstatningstekst (f.eks. Photo_, Camera_)',
    'order_help' => 'Lavere tall behandles først (1 = høyest prioritet)',
    'enabled_help' => '(Kun aktiverte regler blir brukt under omdøping)',

    // Mode options
    'mode_first' => 'Første forekomst',
    'mode_all' => 'Alle forekomster',
    'mode_regex' => 'Regulært uttrykk',
    'mode_trim' => 'Fjern mellomrom',
    'mode_strtolower' => 'små bokstaver',
    'mode_strtoupper' => 'STORE BOKSTAVER',
    'mode_ucwords' => 'Stor forbokstav i hvert ord',
    'mode_ucfirst' => 'Stor forbokstav',

    'mode_first_description' => 'Erstatt kun første forekomst',
    'mode_all_description' => 'Erstatt alle forekomster',
    'mode_regex_description' => 'Bruk mønstersamsvar med regulære uttrykk',
    'mode_trim_description' => 'Fjern mellomrom',
    'mode_strtolower_description' => 'Konverter tekst til små bokstaver',
    'mode_strtoupper_description' => 'Konverter tekst til STORE BOKSTAVER',
    'mode_ucwords_description' => 'Stor forbokstav i hvert ord',
    'mode_ucfirst_description' => 'Stor forbokstav kun på første bokstav',

    'regex_help' => 'Bruk regulære uttrykk for å matche mønstre. For eksempel, for å erstatte <code>IMG_1234.jpeg</code> med <code>1234_JPG.jpeg</code>, kan du bruke <code>/IMG_(\\d+)/</code> som søk og <code>$1_JPG</code> som erstatning. Du finner flere forklaringer og eksempler i lenkene nedenfor.',

    // Buttons
    'cancel' => 'Avbryt',
    'create' => 'Opprett',
    'update' => 'Oppdater',
    'create_first_rule' => 'Opprett din første regel',

    // Validation messages
    'rule_name_required' => 'Regelnavn er påkrevd',
    'pattern_required' => 'Mønster er påkrevd',
    'replacement_required' => 'Erstatning er påkrevd',
    'mode_required' => 'Modus er påkrevd',
    'order_positive' => 'Rekkefølge må være et positivt tall',

    // Success messages
    'rule_created' => 'Omdøpingsregel opprettet',
    'rule_updated' => 'Omdøpingsregel oppdatert',
    'rule_deleted' => 'Omdøpingsregel slettet',

    // Error messages
    'failed_to_create' => 'Kunne ikke opprette omdøpingsregel',
    'failed_to_update' => 'Kunne ikke oppdatere omdøpingsregel',
    'failed_to_delete' => 'Kunne ikke slette omdøpingsregel',
    'failed_to_load' => 'Kunne ikke laste inn omdøpingsregler',

    // List view
    'rules_count' => ':count regler',
    'no_rules' => 'Ingen omdøpingsregler funnet',
    'loading' => 'Laster omdøpingsregler…',
    'pattern_label' => 'Mønster',
    'replace_with_label' => 'Erstatt med',
    'photo' => 'Bilde',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Bekreft sletting',
    'confirm_delete_message' => 'Er du sikker på at du vil slette regelen «:rule»?',
    'delete' => 'Slett',

    // Status messages
    'success' => 'Vellykket',
    'error' => 'Feil',

    // Placeholders
    'select_mode' => 'Velg omdøpingsmodus',
    'execution_order' => 'Kjørerekkefølge',

    // Test functionality
    'test_input_placeholder' => 'Skriv inn et filnavn for å teste omdøpingsreglene dine (f.eks. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Resultat',
    'test_failed' => 'Kunne ikke teste omdøpingsregler',
    'apply_photo_rules' => 'Bruk bilderegler',
    'apply_album_rules' => 'Bruk albumregler',
];
