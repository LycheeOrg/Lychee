<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Pravidla pro přejmenování',

    // Modal titles
    'create_rule' => 'Vytvořit pravidlo pro přejmenování',
    'edit_rule' => 'Upravit pravidlo pro přejmenování',

    // Form fields
    'rule_name' => 'Jméno pravidla',
    'description' => 'Popis',
    'pattern' => 'Vzor',
    'replacement' => 'Nahradit',
    'mode' => 'Režim',
    'order' => 'Pořadí',
    'enabled' => 'Povoleno',
    'photo_rule' => 'Pravidlo aplikovat na fotografie',
    'album_rule' => 'Pravidlo aplikovat na Alba',

    // Form placeholders and help text
    'description_placeholder' => 'Volitelný popis funkce tohoto pravidla',
    'pattern_help' => 'Vzor pro vyhledávání (např. IMG_, DSC_)',
    'replacement_help' => 'Náhradní text (např. Photo_, Camera_)',
    'order_help' => 'Nižší čísla se zpracovávají jako první (1 = nejvyšší priorita)',
    'enabled_help' => '(Při přejmenovávání se použijí pouze povolená pravidla)',

    // Mode options
    'mode_first' => 'První výskyt',
    'mode_all' => 'Všechny výskyty',
    'mode_regex' => 'Regulární výraz',
    'mode_trim' => 'Odstranit mezery',
    'mode_strtolower' => 'malá písmena',
    'mode_strtoupper' => 'VELKÁ PÍSMENA',
    'mode_ucwords' => 'Každé slovo velkými písmeny',
    'mode_ucfirst' => 'První písmeno velkým písmenem',

    'mode_first_description' => 'Nahradit pouze první výskyt',
    'mode_all_description' => 'Nahradit všechny výskyty',
    'mode_regex_description' => 'Použít shodu s regulárním výrazem',
    'mode_trim_description' => 'Oříznout mezery',
    'mode_strtolower_description' => 'Převést řetězec na malá písmena',
    'mode_strtoupper_description' => 'Převést řetězec na VELKÁ PÍSMENA',
    'mode_ucwords_description' => 'Zvětšit první písmeno každého slova',
    'mode_ucfirst_description' => 'Zvětšit pouze první písmeno',

    'regex_help' => 'K porovnání vzorů použijte regulární výrazy. Chcete-li například nahradit <code>IMG_1234.jpeg</code> za <code>1234_JPG.jpeg</code>, můžete jako hledaný řetězec použít <code>/IMG_(\d+)/</code> a jako náhradu <code>$1_JPG</code>. Další vysvětlení a příklady najdete na následujících odkazech.',

    // Buttons
    'cancel' => 'Zrušit',
    'create' => 'Vytvořit',
    'update' => 'Aktualizovat',
    'create_first_rule' => 'Vytvořte své první pravidlo',

    // Validation messages
    'rule_name_required' => 'Název pravidla je povinný',
    'pattern_required' => 'Vzor je povinný',
    'replacement_required' => 'Náhrada je povinná',
    'mode_required' => 'Režim je povinný',
    'order_positive' => 'Pořadí musí být kladné číslo',

    // Success messages
    'rule_created' => 'Úspěšně vytvořeno pravidlo pro přejmenování',
    'rule_updated' => 'Úspěšně aktualizováno pravidlo pro přejmenování',
    'rule_deleted' => 'Úspěšně smazáno pravidlo pro přejmenování',

    // Error messages
    'failed_to_create' => 'Nepodařilo se vytvořit pravidlo pro přejmenování',
    'failed_to_update' => 'Nepodařilo se aktualizovat pravidlo pro přejmenování',
    'failed_to_delete' => 'Nepodařilo se odstranit pravidlo pro přejmenování',
    'failed_to_load' => 'Nepodařilo se načíst pravidla pro přejmenování',

    // List view
    'rules_count' => ':count pravidel',
    'no_rules' => 'Nenalezena žádná pravidla pro přejmenování',
    'loading' => 'Načítání pravidel pro přejmenování...',
    'pattern_label' => 'Vzor',
    'replace_with_label' => 'Nahradit',
    'photo' => 'Fotografie',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Potvrdit smazání',
    'confirm_delete_message' => 'Opravdu chcete smazat pravidlo „:rule“?',
    'delete' => 'Smazat',

    // Status messages
    'success' => 'Úspěch',
    'error' => 'Chyba',

    // Placeholders
    'select_mode' => 'Vyberte režim přejmenování',
    'execution_order' => 'Pořadí provedení',

    // Test functionality
    'test_input_placeholder' => 'Zadejte název souboru pro otestování pravidel přejmenování (např. IMG_1234.jpg)',
    'test_original' => 'Originál',
    'test_result' => 'Výsledek',
    'test_failed' => 'Testování pravidel přejmenování se nezdařilo',
    'apply_photo_rules' => 'Použít pravidla pro fotografie',
    'apply_album_rules' => 'Použít pravidla pro alba',
];
