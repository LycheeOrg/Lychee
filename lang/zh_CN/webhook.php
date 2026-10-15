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
    'description' => '配置在照片被添加、移动或删除时触发的外发 Webhook。',

    // Empty state
    'no_webhooks' => '尚未配置 Webhook。',
    'create_first' => '创建您的第一个 Webhook',

    // Table columns
    'col_name' => '名称',
    'col_event' => '事件',
    'col_method' => '方法',
    'col_url' => 'URL',
    'col_format' => '格式',
    'col_enabled' => '已启用',
    'col_actions' => '操作',

    // Event labels
    'event_photo_add' => '照片已添加',
    'event_photo_move' => '照片已移动',
    'event_photo_delete' => '照片已删除',

    // Payload format labels
    'format_json' => 'JSON',
    'format_query_string' => '查询字符串',

    // Buttons
    'create' => '创建 Webhook',
    'edit' => '编辑',
    'delete' => '删除',
    'cancel' => '取消',
    'save' => '保存',

    // Form fields
    'field_name' => '名称',
    'field_name_placeholder' => '例如：我的 Webhook',
    'field_event' => '事件',
    'field_method' => 'HTTP 方法',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com/hook',
    'field_format' => 'Payload 格式',
    'field_enabled' => '已启用',
    'field_secret' => '密钥',
    'field_secret_placeholder' => '留空以保留现有密钥',
    'field_secret_header' => '密钥请求头',
    'field_secret_header_placeholder' => 'X-Webhook-Secret',
    'field_send_photo_id' => '发送照片 ID',
    'field_send_album_id' => '发送相册 ID',
    'field_send_title' => '发送标题',
    'field_send_size_variants' => '发送尺寸变体',

    // Modal titles
    'modal_create_title' => '创建 Webhook',
    'modal_edit_title' => '编辑 Webhook',

    // Delete confirmation
    'confirm_delete_header' => '删除 Webhook',
    'confirm_delete_message' => '确定要删除 Webhook ":name" 吗？此操作无法撤销。',
    'delete_warning' => '此操作无法撤销。',

    // Toasts
    'created' => 'Webhook 创建成功。',
    'updated' => 'Webhook 更新成功。',
    'deleted' => 'Webhook 删除成功。',
    'error_load' => '加载 Webhook 失败。',
    'error_save' => '保存 Webhook 失败。',
    'error_delete' => '删除 Webhook 失败。',

    // Secret badge
    'has_secret' => '已设置密钥',
    'no_secret' => '无密钥',
];
