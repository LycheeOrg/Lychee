<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Pravidlá premenovania',

    // Modal titles
    'create_rule' => 'Vytvoriť pravidlo premenovania',
    'edit_rule' => 'Upraviť pravidlo premenovania',

    // Form fields
    'rule_name' => 'Názov pravidla',
    'description' => 'Popis',
    'pattern' => 'Vzor',
    'replacement' => 'Náhrada',
    'mode' => 'Režim',
    'order' => 'Poradie',
    'enabled' => 'Povolené',
    'photo_rule' => 'Pravidlo pre fotografie',
    'album_rule' => 'Pravidlo pre albumy',

    // Form placeholders and help text
    'description_placeholder' => 'Voliteľný popis toho, čo toto pravidlo robí',
    'pattern_help' => 'Vzor na vyhľadanie (napr. IMG_, DSC_)',
    'replacement_help' => 'Text náhrady (napr. Photo_, Camera_)',
    'order_help' => 'Nižšie čísla sa spracujú skôr (1 = najvyššia priorita)',
    'enabled_help' => '(Pri premenovaní sa použijú iba povolené pravidlá)',

    // Mode options
    'mode_first' => 'Prvý výskyt',
    'mode_all' => 'Všetky výskyty',
    'mode_regex' => 'Regulárny výraz',
    'mode_trim' => 'Orezať medzery',
    'mode_strtolower' => 'malé písmená',
    'mode_strtoupper' => 'VEĽKÉ PÍSMENÁ',
    'mode_ucwords' => 'Veľké Začiatočné Písmená',
    'mode_ucfirst' => 'Veľké prvé písmeno',

    'mode_first_description' => 'Nahradiť iba prvý výskyt',
    'mode_all_description' => 'Nahradiť všetky výskyty',
    'mode_regex_description' => 'Použiť vyhľadávanie pomocou regulárneho výrazu',
    'mode_trim_description' => 'Orezať medzery',
    'mode_strtolower_description' => 'Previesť reťazec na malé písmená',
    'mode_strtoupper_description' => 'Previesť reťazec na VEĽKÉ PÍSMENÁ',
    'mode_ucwords_description' => 'Veľké začiatočné písmeno každého slova',
    'mode_ucfirst_description' => 'Veľké iba prvé písmeno',

    'regex_help' => 'Na vyhľadávanie vzorov použite regulárne výrazy. Ak napríklad chcete nahradiť <code>IMG_1234.jpeg</code> za <code>1234_JPG.jpeg</code>, môžete ako hľadaný výraz použiť <code>/IMG_(\\d+)/</code> a ako náhradu <code>$1_JPG</code>. Ďalšie vysvetlenia a príklady nájdete na nasledujúcich odkazoch.',

    // Buttons
    'cancel' => 'Zrušiť',
    'create' => 'Vytvoriť',
    'update' => 'Aktualizovať',
    'create_first_rule' => 'Vytvorte svoje prvé pravidlo',

    // Validation messages
    'rule_name_required' => 'Názov pravidla je povinný',
    'pattern_required' => 'Vzor je povinný',
    'replacement_required' => 'Náhrada je povinná',
    'mode_required' => 'Režim je povinný',
    'order_positive' => 'Poradie musí byť kladné číslo',

    // Success messages
    'rule_created' => 'Pravidlo premenovania bolo úspešne vytvorené',
    'rule_updated' => 'Pravidlo premenovania bolo úspešne aktualizované',
    'rule_deleted' => 'Pravidlo premenovania bolo úspešne odstránené',

    // Error messages
    'failed_to_create' => 'Pravidlo premenovania sa nepodarilo vytvoriť',
    'failed_to_update' => 'Pravidlo premenovania sa nepodarilo aktualizovať',
    'failed_to_delete' => 'Pravidlo premenovania sa nepodarilo odstrániť',
    'failed_to_load' => 'Pravidlá premenovania sa nepodarilo načítať',

    // List view
    'rules_count' => ':count pravidiel',
    'no_rules' => 'Nenašli sa žiadne pravidlá premenovania',
    'loading' => 'Načítavajú sa pravidlá premenovania...',
    'pattern_label' => 'Vzor',
    'replace_with_label' => 'Nahradiť za',
    'photo' => 'Fotografia',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Potvrdiť odstránenie',
    'confirm_delete_message' => 'Naozaj chcete odstrániť pravidlo ":rule"?',
    'delete' => 'Odstrániť',

    // Status messages
    'success' => 'Úspech',
    'error' => 'Chyba',

    // Placeholders
    'select_mode' => 'Vyberte režim premenovania',
    'execution_order' => 'Poradie vykonávania',

    // Test functionality
    'test_input_placeholder' => 'Zadajte názov súboru na otestovanie pravidiel premenovania (napr. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Výsledok',
    'test_failed' => 'Pravidlá premenovania sa nepodarilo otestovať',
    'apply_photo_rules' => 'Použiť pravidlá pre fotografie',
    'apply_album_rules' => 'Použiť pravidlá pre albumy',
];
