<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Правила переименования',

    // Modal titles
    'create_rule' => 'Создать правило переименования',
    'edit_rule' => 'Изменить правило переименования',

    // Form fields
    'rule_name' => 'Название правила',
    'description' => 'Описание',
    'pattern' => 'Шаблон',
    'replacement' => 'Замена',
    'mode' => 'Режим',
    'order' => 'Порядок',
    'enabled' => 'Включено',
    'photo_rule' => 'Правило для фотографий',
    'album_rule' => 'Правило для альбомов',

    // Form placeholders and help text
    'description_placeholder' => 'Необязательное описание того, что делает это правило',
    'pattern_help' => 'Шаблон для поиска (например, IMG_, DSC_)',
    'replacement_help' => 'Текст замены (например, Photo_, Camera_)',
    'order_help' => 'Меньшие номера обрабатываются первыми (1 = наивысший приоритет)',
    'enabled_help' => '(При переименовании применяются только включённые правила)',

    // Mode options
    'mode_first' => 'Первое вхождение',
    'mode_all' => 'Все вхождения',
    'mode_regex' => 'Регулярное выражение',
    'mode_trim' => 'Удалить пробелы по краям',
    'mode_strtolower' => 'строчные буквы',
    'mode_strtoupper' => 'ПРОПИСНЫЕ БУКВЫ',
    'mode_ucwords' => 'Каждое Слово С Прописной',
    'mode_ucfirst' => 'Первая буква прописная',

    'mode_first_description' => 'Заменить только первое вхождение',
    'mode_all_description' => 'Заменить все вхождения',
    'mode_regex_description' => 'Использовать поиск по регулярному выражению',
    'mode_trim_description' => 'Удалить пробелы в начале и в конце',
    'mode_strtolower_description' => 'Преобразовать строку в строчные буквы',
    'mode_strtoupper_description' => 'Преобразовать строку в ПРОПИСНЫЕ БУКВЫ',
    'mode_ucwords_description' => 'Сделать первую букву каждого слова прописной',
    'mode_ucfirst_description' => 'Сделать прописной только первую букву',

    'regex_help' => 'Используйте регулярные выражения для поиска по шаблону. Например, чтобы заменить <code>IMG_1234.jpeg</code> на <code>1234_JPG.jpeg</code>, укажите <code>/IMG_(\\d+)/</code> в качестве шаблона и <code>$1_JPG</code> в качестве замены. Дополнительные пояснения и примеры можно найти по ссылкам ниже.',

    // Buttons
    'cancel' => 'Отмена',
    'create' => 'Создать',
    'update' => 'Обновить',
    'create_first_rule' => 'Создайте первое правило',

    // Validation messages
    'rule_name_required' => 'Укажите название правила',
    'pattern_required' => 'Укажите шаблон',
    'replacement_required' => 'Укажите замену',
    'mode_required' => 'Выберите режим',
    'order_positive' => 'Порядок должен быть положительным числом',

    // Success messages
    'rule_created' => 'Правило переименования успешно создано',
    'rule_updated' => 'Правило переименования успешно обновлено',
    'rule_deleted' => 'Правило переименования успешно удалено',

    // Error messages
    'failed_to_create' => 'Не удалось создать правило переименования',
    'failed_to_update' => 'Не удалось обновить правило переименования',
    'failed_to_delete' => 'Не удалось удалить правило переименования',
    'failed_to_load' => 'Не удалось загрузить правила переименования',

    // List view
    'rules_count' => 'Правил: :count',
    'no_rules' => 'Правила переименования не найдены',
    'loading' => 'Загрузка правил переименования...',
    'pattern_label' => 'Шаблон',
    'replace_with_label' => 'Заменить на',
    'photo' => 'Фото',
    'album' => 'Альбом',

    // Delete confirmation
    'confirm_delete_header' => 'Подтверждение удаления',
    'confirm_delete_message' => 'Вы уверены, что хотите удалить правило ":rule"?',
    'delete' => 'Удалить',

    // Status messages
    'success' => 'Успешно',
    'error' => 'Ошибка',

    // Placeholders
    'select_mode' => 'Выберите режим переименования',
    'execution_order' => 'Порядок выполнения',

    // Test functionality
    'test_input_placeholder' => 'Введите имя файла для проверки правил переименования (например, IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Результат',
    'test_failed' => 'Не удалось проверить правила переименования',
    'apply_photo_rules' => 'Применить правила для фото',
    'apply_album_rules' => 'Применить правила для альбомов',
];
