<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => '重新命名規則',

    // Modal titles
    'create_rule' => '建立重新命名規則',
    'edit_rule' => '編輯重新命名規則',

    // Form fields
    'rule_name' => '規則名稱',
    'description' => '描述',
    'pattern' => '比對模式',
    'replacement' => '取代內容',
    'mode' => '模式',
    'order' => '順序',
    'enabled' => '已啟用',
    'photo_rule' => '套用於照片的規則',
    'album_rule' => '套用於相簿的規則',

    // Form placeholders and help text
    'description_placeholder' => '選填，說明此規則的用途',
    'pattern_help' => '要比對的模式（例如 IMG_、DSC_）',
    'replacement_help' => '取代文字（例如 Photo_、Camera_）',
    'order_help' => '數字越小越先處理（1 = 最高優先順序）',
    'enabled_help' => '（重新命名時只會套用已啟用的規則）',

    // Mode options
    'mode_first' => '第一個符合項目',
    'mode_all' => '所有符合項目',
    'mode_regex' => '正規表示式',
    'mode_trim' => '移除前後空白',
    'mode_strtolower' => '全部小寫',
    'mode_strtoupper' => '全部大寫',
    'mode_ucwords' => '每個單字首字母大寫',
    'mode_ucfirst' => '首字母大寫',

    'mode_first_description' => '只取代第一個符合項目',
    'mode_all_description' => '取代所有符合項目',
    'mode_regex_description' => '使用正規表示式比對',
    'mode_trim_description' => '移除前後空白',
    'mode_strtolower_description' => '將字串轉換為小寫',
    'mode_strtoupper_description' => '將字串轉換為大寫',
    'mode_ucwords_description' => '將每個單字的首字母大寫',
    'mode_ucfirst_description' => '只將第一個字母大寫',

    'regex_help' => '使用正規表示式比對模式。例如，若要將 <code>IMG_1234.jpeg</code> 取代為 <code>1234_JPG.jpeg</code>，可以使用 <code>/IMG_(\\d+)/</code> 作為比對模式，並以 <code>$1_JPG</code> 作為取代內容。您可以在以下連結中找到更多說明與範例。',

    // Buttons
    'cancel' => '取消',
    'create' => '建立',
    'update' => '更新',
    'create_first_rule' => '建立您的第一條規則',

    // Validation messages
    'rule_name_required' => '規則名稱為必填',
    'pattern_required' => '比對模式為必填',
    'replacement_required' => '取代內容為必填',
    'mode_required' => '模式為必填',
    'order_positive' => '順序必須為正數',

    // Success messages
    'rule_created' => '已成功建立重新命名規則',
    'rule_updated' => '已成功更新重新命名規則',
    'rule_deleted' => '已成功刪除重新命名規則',

    // Error messages
    'failed_to_create' => '建立重新命名規則失敗',
    'failed_to_update' => '更新重新命名規則失敗',
    'failed_to_delete' => '刪除重新命名規則失敗',
    'failed_to_load' => '載入重新命名規則失敗',

    // List view
    'rules_count' => ':count 條規則',
    'no_rules' => '找不到重新命名規則',
    'loading' => '正在載入重新命名規則…',
    'pattern_label' => '比對模式',
    'replace_with_label' => '取代為',
    'photo' => '照片',
    'album' => '相簿',

    // Delete confirmation
    'confirm_delete_header' => '確認刪除',
    'confirm_delete_message' => '確定要刪除規則「:rule」嗎？',
    'delete' => '刪除',

    // Status messages
    'success' => '成功',
    'error' => '錯誤',

    // Placeholders
    'select_mode' => '選擇重新命名模式',
    'execution_order' => '執行順序',

    // Test functionality
    'test_input_placeholder' => '輸入檔名以測試您的重新命名規則（例如 IMG_1234.jpg）',
    'test_original' => 'Original',
    'test_result' => '結果',
    'test_failed' => '測試重新命名規則失敗',
    'apply_photo_rules' => '套用照片規則',
    'apply_album_rules' => '套用相簿規則',
];
