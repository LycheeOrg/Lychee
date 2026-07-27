<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Hernoemregels',

    // Modal titles
    'create_rule' => 'Hernoemregel aanmaken',
    'edit_rule' => 'Hernoemregel bewerken',

    // Form fields
    'rule_name' => 'Regelnaam',
    'description' => 'Beschrijving',
    'pattern' => 'Patroon',
    'replacement' => 'Vervanging',
    'mode' => 'Modus',
    'order' => 'Volgorde',
    'enabled' => 'Ingeschakeld',
    'photo_rule' => 'Regel toegepast op foto’s',
    'album_rule' => 'Regel toegepast op albums',

    // Form placeholders and help text
    'description_placeholder' => 'Optionele beschrijving van wat deze regel doet',
    'pattern_help' => 'Patroon om te matchen (bijv. IMG_, DSC_)',
    'replacement_help' => 'Vervangende tekst (bijv. Photo_, Camera_)',
    'order_help' => 'Lagere getallen worden eerst verwerkt (1 = hoogste prioriteit)',
    'enabled_help' => '(Alleen ingeschakelde regels worden toegepast tijdens het hernoemen)',

    // Mode options
    'mode_first' => 'Eerste voorkomen',
    'mode_all' => 'Alle voorkomens',
    'mode_regex' => 'Reguliere expressie',
    'mode_trim' => 'Spaties verwijderen',
    'mode_strtolower' => 'kleine letters',
    'mode_strtoupper' => 'HOOFDLETTERS',
    'mode_ucwords' => 'Elk Woord Met Hoofdletter',
    'mode_ucfirst' => 'Eerste letter met hoofdletter',

    'mode_first_description' => 'Vervang alleen het eerste voorkomen',
    'mode_all_description' => 'Vervang alle voorkomens',
    'mode_regex_description' => 'Gebruik matching met reguliere expressies',
    'mode_trim_description' => 'Verwijder spaties aan begin en eind',
    'mode_strtolower_description' => 'Zet tekst om naar kleine letters',
    'mode_strtoupper_description' => 'Zet tekst om naar HOOFDLETTERS',
    'mode_ucwords_description' => 'Zet de eerste letter van elk woord in hoofdletter',
    'mode_ucfirst_description' => 'Zet alleen de eerste letter in hoofdletter',

    'regex_help' => 'Gebruik reguliere expressies om patronen te matchen. Om bijvoorbeeld <code>IMG_1234.jpeg</code> te vervangen door <code>1234_JPG.jpeg</code>, kunt u <code>/IMG_(\d+)/</code> als zoekpatroon en <code>$1_JPG</code> als vervanging gebruiken. Meer uitleg en voorbeelden vindt u via de onderstaande links.',

    // Buttons
    'cancel' => 'Annuleren',
    'create' => 'Aanmaken',
    'update' => 'Bijwerken',
    'create_first_rule' => 'Maak uw eerste regel aan',

    // Validation messages
    'rule_name_required' => 'Regelnaam is verplicht',
    'pattern_required' => 'Patroon is verplicht',
    'replacement_required' => 'Vervanging is verplicht',
    'mode_required' => 'Modus is verplicht',
    'order_positive' => 'Volgorde moet een positief getal zijn',

    // Success messages
    'rule_created' => 'Hernoemregel succesvol aangemaakt',
    'rule_updated' => 'Hernoemregel succesvol bijgewerkt',
    'rule_deleted' => 'Hernoemregel succesvol verwijderd',

    // Error messages
    'failed_to_create' => 'Aanmaken van hernoemregel mislukt',
    'failed_to_update' => 'Bijwerken van hernoemregel mislukt',
    'failed_to_delete' => 'Verwijderen van hernoemregel mislukt',
    'failed_to_load' => 'Laden van hernoemregels mislukt',

    // List view
    'rules_count' => ':count regels',
    'no_rules' => 'Geen hernoemregels gevonden',
    'loading' => 'Hernoemregels laden...',
    'pattern_label' => 'Patroon',
    'replace_with_label' => 'Vervangen door',
    'photo' => 'Foto',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Verwijdering bevestigen',
    'confirm_delete_message' => 'Weet u zeker dat u de regel ":rule" wilt verwijderen?',
    'delete' => 'Verwijderen',

    // Status messages
    'success' => 'Succes',
    'error' => 'Fout',

    // Placeholders
    'select_mode' => 'Selecteer hernoemmodus',
    'execution_order' => 'Uitvoeringsvolgorde',

    // Test functionality
    'test_input_placeholder' => 'Voer een bestandsnaam in om uw hernoemregels te testen (bijv. IMG_1234.jpg)',
    'test_original' => 'Origineel',
    'test_result' => 'Resultaat',
    'test_failed' => 'Testen van hernoemregels mislukt',
    'apply_photo_rules' => 'Fotoregels toepassen',
    'apply_album_rules' => 'Albumregels toepassen',
];
