<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Reguły zmiany nazw',

    // Modal titles
    'create_rule' => 'Utwórz regułę zmiany nazw',
    'edit_rule' => 'Edytuj regułę zmiany nazw',

    // Form fields
    'rule_name' => 'Nazwa reguły',
    'description' => 'Opis',
    'pattern' => 'Wzorzec',
    'replacement' => 'Zamiennik',
    'mode' => 'Tryb',
    'order' => 'Kolejność',
    'enabled' => 'Włączona',
    'photo_rule' => 'Reguła stosowana do zdjęć',
    'album_rule' => 'Reguła stosowana do albumów',

    // Form placeholders and help text
    'description_placeholder' => 'Opcjonalny opis działania tej reguły',
    'pattern_help' => 'Wzorzec do dopasowania (np. IMG_, DSC_)',
    'replacement_help' => 'Tekst zastępczy (np. Photo_, Camera_)',
    'order_help' => 'Niższe liczby są przetwarzane jako pierwsze (1 = najwyższy priorytet)',
    'enabled_help' => '(Podczas zmiany nazw stosowane są tylko włączone reguły)',

    // Mode options
    'mode_first' => 'Pierwsze wystąpienie',
    'mode_all' => 'Wszystkie wystąpienia',
    'mode_regex' => 'Wyrażenie regularne',
    'mode_trim' => 'Usuń białe znaki',
    'mode_strtolower' => 'małe litery',
    'mode_strtoupper' => 'WIELKIE LITERY',
    'mode_ucwords' => 'Wielka Litera Każdego Słowa',
    'mode_ucfirst' => 'Wielka pierwsza litera',

    'mode_first_description' => 'Zastąp tylko pierwsze wystąpienie',
    'mode_all_description' => 'Zastąp wszystkie wystąpienia',
    'mode_regex_description' => 'Użyj dopasowania wyrażeniem regularnym',
    'mode_trim_description' => 'Usuń białe znaki z początku i końca',
    'mode_strtolower_description' => 'Zamień tekst na małe litery',
    'mode_strtoupper_description' => 'Zamień tekst na WIELKIE LITERY',
    'mode_ucwords_description' => 'Zacznij każde słowo wielką literą',
    'mode_ucfirst_description' => 'Zacznij wielką literą tylko pierwszy znak',

    'regex_help' => 'Użyj wyrażeń regularnych do dopasowywania wzorców. Na przykład, aby zamienić <code>IMG_1234.jpeg</code> na <code>1234_JPG.jpeg</code>, możesz użyć <code>/IMG_(\\d+)/</code> jako wzorca i <code>$1_JPG</code> jako zamiennika. Więcej wyjaśnień i przykładów znajdziesz pod poniższymi linkami.',

    // Buttons
    'cancel' => 'Anuluj',
    'create' => 'Utwórz',
    'update' => 'Aktualizuj',
    'create_first_rule' => 'Utwórz pierwszą regułę',

    // Validation messages
    'rule_name_required' => 'Nazwa reguły jest wymagana',
    'pattern_required' => 'Wzorzec jest wymagany',
    'replacement_required' => 'Zamiennik jest wymagany',
    'mode_required' => 'Tryb jest wymagany',
    'order_positive' => 'Kolejność musi być liczbą dodatnią',

    // Success messages
    'rule_created' => 'Reguła zmiany nazw została utworzona',
    'rule_updated' => 'Reguła zmiany nazw została zaktualizowana',
    'rule_deleted' => 'Reguła zmiany nazw została usunięta',

    // Error messages
    'failed_to_create' => 'Nie udało się utworzyć reguły zmiany nazw',
    'failed_to_update' => 'Nie udało się zaktualizować reguły zmiany nazw',
    'failed_to_delete' => 'Nie udało się usunąć reguły zmiany nazw',
    'failed_to_load' => 'Nie udało się wczytać reguł zmiany nazw',

    // List view
    'rules_count' => 'Reguły: :count',
    'no_rules' => 'Nie znaleziono reguł zmiany nazw',
    'loading' => 'Wczytywanie reguł zmiany nazw...',
    'pattern_label' => 'Wzorzec',
    'replace_with_label' => 'Zastąp przez',
    'photo' => 'Zdjęcie',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Potwierdź usunięcie',
    'confirm_delete_message' => 'Czy na pewno chcesz usunąć regułę ":rule"?',
    'delete' => 'Usuń',

    // Status messages
    'success' => 'Sukces',
    'error' => 'Błąd',

    // Placeholders
    'select_mode' => 'Wybierz tryb zmiany nazw',
    'execution_order' => 'Kolejność wykonywania',

    // Test functionality
    'test_input_placeholder' => 'Wpisz nazwę pliku, aby przetestować reguły zmiany nazw (np. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Wynik',
    'test_failed' => 'Nie udało się przetestować reguł zmiany nazw',
    'apply_photo_rules' => 'Zastosuj reguły dla zdjęć',
    'apply_album_rules' => 'Zastosuj reguły dla albumów',
];
