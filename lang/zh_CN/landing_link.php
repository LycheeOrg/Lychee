<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Landing page extra links (admin CRUD)
    |--------------------------------------------------------------------------
    */

    'title' => '额外链接',
    'description' => '管理在落地页导航栏和/或页脚中显示的任意有序额外链接列表。',

    // Empty state
    'no_links' => '尚未配置额外链接。',
    'create_first' => '创建您的第一个链接',

    // Table columns
    'col_label' => '标签',
    'col_url' => 'URL',
    'col_placement' => '位置',
    'col_enabled' => '已启用',
    'col_actions' => '操作',

    // Placement labels
    'placement_nav' => '导航栏',
    'placement_footer' => '页脚',
    'placement_both' => '导航栏和页脚',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => '内置',
    'built_in_cannot_delete' => '此内置链接无法删除。您可以将其禁用，或像其他链接一样调整其顺序。',
    'built_in_url_hint' => '这是一个内置链接，其目标无法更改。',

    // Buttons
    'create' => '创建链接',
    'edit' => '编辑',
    'delete' => '删除',
    'cancel' => '取消',
    'save' => '保存',

    // Form fields
    'field_label' => '标签',
    'field_label_placeholder' => '例如 Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => '位置',
    'field_open_in_new_tab' => '在新标签页中打开',
    'field_enabled' => '已启用',

    // Modal titles
    'modal_create_title' => '创建链接',
    'modal_edit_title' => '编辑链接',

    // Delete confirmation
    'confirm_delete_header' => '删除链接',
    'confirm_delete_message' => '确定要删除链接“:label”吗？此操作无法撤销。',

    // Toasts
    'created' => '链接创建成功。',
    'updated' => '链接更新成功。',
    'deleted' => '链接删除成功。',
    'reordered' => '链接排序成功。',
    'error_load' => '加载链接失败。',
    'error_save' => '保存链接失败。',
    'error_delete' => '删除链接失败。',
    'error_reorder' => '链接排序失败。',
];
