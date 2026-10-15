<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook admin page
    |--------------------------------------------------------------------------
    */

    'title' => 'Webhooks',
    'description' => '設定在新增、移動或刪除相片時觸發的外送 Webhook。',

    // Empty state
    'no_webhooks' => '尚未設定任何 Webhook。',
    'create_first' => '建立您的第一個 Webhook',

    // Table columns
    'col_name' => '名稱',
    'col_event' => '事件',
    'col_method' => '方法',
    'col_url' => 'URL',
    'col_format' => '格式',
    'col_enabled' => '已啟用',
    'col_actions' => '操作',

    // Event labels
    'event_photo_add' => '相片已新增',
    'event_photo_move' => '相片已移動',
    'event_photo_delete' => '相片已刪除',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => '查詢字串',

    // Buttons
    'create' => '建立 Webhook',
    'edit' => '編輯',
    'delete' => '刪除',
    'cancel' => '取消',
    'save' => '儲存',

    // Form fields
    'field_name' => '名稱',
    'field_name_placeholder' => '例如：我的 Webhook',
    'field_event' => '事件',
    'field_method' => 'HTTP 方法',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload 格式',
    'field_enabled' => '已啟用',
    'field_secret' => '密鑰',
    'field_secret_placeholder' => '留空以保留現有密鑰',
    'field_secret_header' => '密鑰標頭',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => '傳送相片 ID',
    'field_send_album_id' => '傳送相簿 ID',
    'field_send_title' => '傳送標題',
    'field_send_size_variants' => '傳送尺寸版本',

    // Modal titles
    'modal_create_title' => '建立 Webhook',
    'modal_edit_title' => '編輯 Webhook',

    // Delete confirmation
    'confirm_delete_header' => '刪除 Webhook',
    'confirm_delete_message' => '確定要刪除 Webhook「:name」嗎？此操作無法復原。',
    'delete_warning' => '此操作無法復原。',

    // Toasts
    'created' => 'Webhook 已成功建立。',
    'updated' => 'Webhook 已成功更新。',
    'deleted' => 'Webhook 已成功刪除。',
    'error_load' => '載入 Webhook 失敗。',
    'error_save' => '儲存 Webhook 失敗。',
    'error_delete' => '刪除 Webhook 失敗。',

    // Secret badge
    'has_secret' => '已設定密鑰',
    'no_secret' => '無密鑰',
];
