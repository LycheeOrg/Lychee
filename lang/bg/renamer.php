<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Правила за преименуване',

    // Modal titles
    'create_rule' => 'Създай правило за преименуване',
    'edit_rule' => 'Редактирай правило за преименуване',

    // Form fields
    'rule_name' => 'Име на правило',
    'description' => 'Описание',
    'pattern' => 'Шаблон',
    'replacement' => 'Замяна',
    'mode' => 'Режим',
    'order' => 'Пореден номер',
    'enabled' => 'Активно',
    'photo_rule' => 'Правилото се прилага за снимки',
    'album_rule' => 'Правилото се прилага за албуми',

    // Form placeholders and help text
    'description_placeholder' => 'По избор описание на действието на правилото',
    'pattern_help' => 'Шаблон за съвпадение (напр. IMG_, DSC_)',
    'replacement_help' => 'Текст за замяна (напр. Photo_, Camera_)',
    'order_help' => 'По-ниските числа се обработват първо (1 = най-висок приоритет)',
    'enabled_help' => '(Само активните правила ще се прилагат при преименуване)',

    // Mode options
    'mode_first' => 'Първо срещане',
    'mode_all' => 'Всички срещания',
    'mode_regex' => 'Регулярен израз',
    'mode_trim' => 'Премахни интервали',
    'mode_strtolower' => 'малки букви',
    'mode_strtoupper' => 'ГЛАВНИ БУКВИ',
    'mode_ucwords' => 'Главни букви на всяка дума',
    'mode_ucfirst' => 'Главна буква на първата дума',

    'mode_first_description' => 'Замени само първото срещане',
    'mode_all_description' => 'Замени всички срещания',
    'mode_regex_description' => 'Използвай съвпадение с regex',
    'mode_trim_description' => 'Премахни интервалите',
    'mode_strtolower_description' => 'Преобразувай текста в малки букви',
    'mode_strtoupper_description' => 'Преобразувай текста в ГЛАВНИ БУКВИ',
    'mode_ucwords_description' => 'Първата буква на всяка дума е главна',
    'mode_ucfirst_description' => 'Първата буква е главна',

    'regex_help' => 'Използвайте регулярни изрази за съвпадение на шаблони. Например, за да замените <code>IMG_1234.jpeg</code> с <code>1234_JPG.jpeg</code>, можете да използвате <code>/IMG_(\d+)/</code> като шаблон и <code>$1_JPG</code> като замяна. Повече обяснения и примери можете да намерите в следните линкове.',

    // Buttons
    'cancel' => 'Откажи',
    'create' => 'Създай',
    'update' => 'Обнови',
    'create_first_rule' => 'Създай първото си правило',

    // Validation messages
    'rule_name_required' => 'Името на правилото е задължително',
    'pattern_required' => 'Шаблонът е задължителен',
    'replacement_required' => 'Замяната е задължителна',
    'mode_required' => 'Режимът е задължителен',
    'order_positive' => 'Пореден номер трябва да е положително число',

    // Success messages
    'rule_created' => 'Правилото за преименуване е създадено успешно',
    'rule_updated' => 'Правилото за преименуване е обновено успешно',
    'rule_deleted' => 'Правилото за преименуване е изтрито успешно',

    // Error messages
    'failed_to_create' => 'Неуспешно създаване на правило за преименуване',
    'failed_to_update' => 'Неуспешно обновяване на правило за преименуване',
    'failed_to_delete' => 'Неуспешно изтриване на правило за преименуване',
    'failed_to_load' => 'Неуспешно зареждане на правилата за преименуване',

    // List view
    'rules_count' => ':count правила',
    'no_rules' => 'Не са намерени правила за преименуване',
    'loading' => 'Зареждане на правилата за преименуване...',
    'pattern_label' => 'Шаблон',
    'replace_with_label' => 'Замени с',
    'photo' => 'Снимка',
    'album' => 'Албум',

    // Delete confirmation
    'confirm_delete_header' => 'Потвърждение за изтриване',
    'confirm_delete_message' => 'Сигурни ли сте, че искате да изтриете правилото ":rule"?',
    'delete' => 'Изтрий',

    // Status messages
    'success' => 'Успех',
    'error' => 'Грешка',

    // Placeholders
    'select_mode' => 'Изберете режим на преименуване',
    'execution_order' => 'Поръчка на изпълнение',

    // Test functionality
    'test_input_placeholder' => 'Въведете име на файл за тестване на правилата (напр. IMG_1234.jpg)',
    'test_original' => 'Оригинал',
    'test_result' => 'Резултат',
    'test_failed' => 'Неуспешен тест на правилата за преименуване',
    'apply_photo_rules' => 'Прилагане на правила за снимки',
    'apply_album_rules' => 'Прилагане на правила за албуми',
];
