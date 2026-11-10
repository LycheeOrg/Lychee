<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => '重命名规则',

    // Modal titles
    'create_rule' => '创建重命名规则',
    'edit_rule' => '编辑重命名规则',

    // Form fields
    'rule_name' => '规则名称',
    'description' => '描述',
    'pattern' => '模式',
    'replacement' => '替换内容',
    'mode' => '模式',
    'order' => '顺序',
    'enabled' => '已启用',
    'photo_rule' => '应用于照片的规则',
    'album_rule' => '应用于相册的规则',

    // Form placeholders and help text
    'description_placeholder' => '可选，描述此规则的作用',
    'pattern_help' => '要匹配的模式（例如 IMG_、DSC_）',
    'replacement_help' => '替换文本（例如 Photo_、Camera_）',
    'order_help' => '数字越小越先处理（1 = 最高优先级）',
    'enabled_help' => '（重命名时仅应用已启用的规则）',

    // Mode options
    'mode_first' => '首次出现',
    'mode_all' => '所有出现',
    'mode_regex' => '正则表达式',
    'mode_trim' => '去除空白',
    'mode_strtolower' => '小写',
    'mode_strtoupper' => '大写',
    'mode_ucwords' => '每个单词首字母大写',
    'mode_ucfirst' => '首字母大写',

    'mode_first_description' => '仅替换第一次出现',
    'mode_all_description' => '替换所有出现',
    'mode_regex_description' => '使用正则表达式模式匹配',
    'mode_trim_description' => '去除首尾空白',
    'mode_strtolower_description' => '将字符串转换为小写',
    'mode_strtoupper_description' => '将字符串转换为大写',
    'mode_ucwords_description' => '每个单词首字母大写',
    'mode_ucfirst_description' => '仅首字母大写',

    'regex_help' => '使用正则表达式匹配模式。例如，要将 <code>IMG_1234.jpeg</code> 替换为 <code>1234_JPG.jpeg</code>，可以使用 <code>/IMG_(\\d+)/</code> 作为匹配模式，并使用 <code>$1_JPG</code> 作为替换内容。您可以在以下链接中找到更多说明和示例。',

    // Buttons
    'cancel' => '取消',
    'create' => '创建',
    'update' => '更新',
    'create_first_rule' => '创建您的第一条规则',

    // Validation messages
    'rule_name_required' => '规则名称为必填项',
    'pattern_required' => '模式为必填项',
    'replacement_required' => '替换内容为必填项',
    'mode_required' => '模式为必填项',
    'order_positive' => '顺序必须为正数',

    // Success messages
    'rule_created' => '重命名规则创建成功',
    'rule_updated' => '重命名规则更新成功',
    'rule_deleted' => '重命名规则删除成功',

    // Error messages
    'failed_to_create' => '创建重命名规则失败',
    'failed_to_update' => '更新重命名规则失败',
    'failed_to_delete' => '删除重命名规则失败',
    'failed_to_load' => '加载重命名规则失败',

    // List view
    'rules_count' => ':count 条规则',
    'no_rules' => '未找到重命名规则',
    'loading' => '正在加载重命名规则...',
    'pattern_label' => '模式',
    'replace_with_label' => '替换为',
    'photo' => '照片',
    'album' => '相册',

    // Delete confirmation
    'confirm_delete_header' => '确认删除',
    'confirm_delete_message' => '确定要删除规则 ":rule" 吗？',
    'delete' => '删除',

    // Status messages
    'success' => '成功',
    'error' => '错误',

    // Placeholders
    'select_mode' => '选择重命名模式',
    'execution_order' => '执行顺序',

    // Test functionality
    'test_input_placeholder' => '输入文件名以测试您的重命名规则（例如 IMG_1234.jpg）',
    'test_original' => 'Original',
    'test_result' => '结果',
    'test_failed' => '测试重命名规则失败',
    'apply_photo_rules' => '应用照片规则',
    'apply_album_rules' => '应用相册规则',
];
