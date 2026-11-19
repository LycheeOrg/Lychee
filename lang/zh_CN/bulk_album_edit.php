<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Bulk Album Edit admin page
    |--------------------------------------------------------------------------
    */

    'title' => '批量编辑相册',
    'description' => '一次性编辑多个相册的元数据和可见性设置。',
    'warning' => '此处的更改会立即生效且无法撤销。标签相册不会显示。',

    // Table columns
    'col_title' => '标题',
    'col_owner' => '所有者',
    'col_license' => '许可证',
    'col_is_nsfw' => '敏感',
    'col_is_public' => '公开',
    'col_is_link_required' => '链接',
    'col_grants_full_photo_access' => '完整照片',
    'col_grants_download' => '下载',
    'col_grants_upload' => '上传',
    'col_photo_sorting' => '照片排序',
    'col_album_sorting' => '相册排序',
    'col_created_at' => '创建时间',
    'taken_range' => '拍摄时间：:range',
    'taken_none' => '无数据',

    // Filter
    'filter_placeholder' => '按标题搜索...',

    // Pagination
    'per_page' => '每页',
    'total_selected' => '已选择 :n 个相册|已选择 :n 个相册',
    'select_all_page' => '选择本页全部',
    'select_all_matching' => '选择所有匹配项',
    'cap_warning' => '仅选择了前 1,000 个相册。',

    // Mode toggle
    'mode_paginated' => '分页',
    'mode_infinite' => '无限滚动',

    // Action buttons
    'action_delete' => '删除',
    'action_set_owner' => '设置所有者',
    'action_edit_fields' => '编辑字段',

    // Edit Fields modal
    'edit_fields_title' => '编辑字段',
    'edit_fields_description' => '仅会更新已勾选的字段。空值将清空该字段。',
    'section_metadata' => '元数据',
    'section_visibility' => '可见性',
    'field_description' => '描述',
    'field_copyright' => '版权',
    'field_license' => '许可证',
    'field_photo_layout' => '照片布局',
    'field_photo_sorting_col' => '照片排序字段',
    'field_photo_sorting_order' => '照片排序顺序',
    'field_album_sorting_col' => '相册排序字段',
    'field_album_sorting_order' => '相册排序顺序',
    'field_album_thumb_aspect_ratio' => '缩略图宽高比',
    'field_album_timeline' => '相册时间线',
    'field_photo_timeline' => '照片时间线',
    'field_published_at' => 'Flow 发布日期',
    'field_is_nsfw' => '敏感',
    'field_is_public' => '公开',
    'field_is_link_required' => '需要链接',
    'field_grants_full_photo_access' => '完整照片访问',
    'field_grants_download' => '下载',
    'field_grants_upload' => '上传（SE）',
    'apply' => '应用',
    'cancel' => '取消',

    // Set Owner modal
    'set_owner_title' => '设置所有者',
    'set_owner_description' => '所有选中的相册将被移至根级别，其下级相册也将一并转移。',
    'set_owner_select_user' => '选择新所有者',
    'transfer' => '转移',

    // Delete confirmation modal
    'delete_title' => '删除相册',
    'delete_confirm' => '您即将永久删除 :count 个相册及其所有子相册和照片。此操作无法撤销。|您即将永久删除 :count 个相册及其所有子相册和照片。此操作无法撤销。',
    'confirm_delete' => '确认删除',

    // Toasts
    'success_patch' => '相册更新成功。',
    'success_set_owner' => '所有权转移成功。',
    'success_delete' => '相册删除成功。',
    'error_load' => '加载相册失败。',
    'error_load_ids' => '加载相册 ID 失败。',
    'error_patch' => '更新相册失败。',
    'error_missing_timezone' => '请为发布日期选择时区。',
    'error_set_owner' => '转移所有权失败。',
    'error_delete' => '删除相册失败。',
    'error_load_users' => '加载用户失败。',
];
