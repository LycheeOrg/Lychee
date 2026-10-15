<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'リネームルール',

    // Modal titles
    'create_rule' => 'リネームルールを作成',
    'edit_rule' => 'リネームルールを編集',

    // Form fields
    'rule_name' => 'ルール名',
    'description' => '説明',
    'pattern' => 'パターン',
    'replacement' => '置換文字列',
    'mode' => 'モード',
    'order' => '順序',
    'enabled' => '有効',
    'photo_rule' => '写真に適用するルール',
    'album_rule' => 'アルバムに適用するルール',

    // Form placeholders and help text
    'description_placeholder' => 'このルールの動作の説明（任意）',
    'pattern_help' => '一致させるパターン（例：IMG_、DSC_）',
    'replacement_help' => '置換テキスト（例：Photo_、Camera_）',
    'order_help' => '数字が小さいほど先に処理されます（1 = 最優先）',
    'enabled_help' => '（有効なルールのみがリネーム時に適用されます）',

    // Mode options
    'mode_first' => '最初の一致',
    'mode_all' => 'すべての一致',
    'mode_regex' => '正規表現',
    'mode_trim' => '空白を除去',
    'mode_strtolower' => '小文字',
    'mode_strtoupper' => '大文字',
    'mode_ucwords' => '各単語の先頭を大文字',
    'mode_ucfirst' => '先頭文字を大文字',

    'mode_first_description' => '最初の一致のみを置換',
    'mode_all_description' => 'すべての一致を置換',
    'mode_regex_description' => '正規表現によるパターンマッチングを使用',
    'mode_trim_description' => '空白を除去',
    'mode_strtolower_description' => '文字列を小文字に変換',
    'mode_strtoupper_description' => '文字列を大文字に変換',
    'mode_ucwords_description' => '各単語の先頭を大文字にする',
    'mode_ucfirst_description' => '先頭文字のみを大文字にする',

    'regex_help' => '正規表現を使用してパターンに一致させます。たとえば、<code>IMG_1234.jpeg</code> を <code>1234_JPG.jpeg</code> に置換するには、検索パターンに <code>/IMG_(\\d+)/</code>、置換文字列に <code>$1_JPG</code> を使用します。詳しい説明や例は以下のリンクを参照してください。',

    // Buttons
    'cancel' => 'キャンセル',
    'create' => '作成',
    'update' => '更新',
    'create_first_rule' => '最初のルールを作成',

    // Validation messages
    'rule_name_required' => 'ルール名は必須です',
    'pattern_required' => 'パターンは必須です',
    'replacement_required' => '置換文字列は必須です',
    'mode_required' => 'モードは必須です',
    'order_positive' => '順序は正の数である必要があります',

    // Success messages
    'rule_created' => 'リネームルールを作成しました',
    'rule_updated' => 'リネームルールを更新しました',
    'rule_deleted' => 'リネームルールを削除しました',

    // Error messages
    'failed_to_create' => 'リネームルールの作成に失敗しました',
    'failed_to_update' => 'リネームルールの更新に失敗しました',
    'failed_to_delete' => 'リネームルールの削除に失敗しました',
    'failed_to_load' => 'リネームルールの読み込みに失敗しました',

    // List view
    'rules_count' => ':count 件のルール',
    'no_rules' => 'リネームルールが見つかりません',
    'loading' => 'リネームルールを読み込んでいます...',
    'pattern_label' => 'パターン',
    'replace_with_label' => '置換後',
    'photo' => '写真',
    'album' => 'アルバム',

    // Delete confirmation
    'confirm_delete_header' => '削除の確認',
    'confirm_delete_message' => 'ルール ":rule" を削除してもよろしいですか？',
    'delete' => '削除',

    // Status messages
    'success' => '成功',
    'error' => 'エラー',

    // Placeholders
    'select_mode' => 'リネームモードを選択',
    'execution_order' => '実行順序',

    // Test functionality
    'test_input_placeholder' => 'リネームルールをテストするファイル名を入力してください（例：IMG_1234.jpg）',
    'test_original' => 'Original',
    'test_result' => '結果',
    'test_failed' => 'リネームルールのテストに失敗しました',
    'apply_photo_rules' => '写真ルールを適用',
    'apply_album_rules' => 'アルバムルールを適用',
];
