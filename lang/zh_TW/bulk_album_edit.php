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

    'title' => '批次編輯相簿',
    'description' => '一次編輯多個相簿的中繼資料與可見性設定。',
    'warning' => '在此所做的變更會立即套用且無法復原。不會顯示標籤相簿。',

    // Table columns
    'col_title' => '標題',
    'col_owner' => '擁有者',
    'col_license' => '授權',
    'col_is_nsfw' => '敏感',
    'col_is_public' => '公開',
    'col_is_link_required' => '連結',
    'col_grants_full_photo_access' => '完整照片',
    'col_grants_download' => '下載',
    'col_grants_upload' => '上傳',
    'col_photo_sorting' => '照片排序',
    'col_album_sorting' => '相簿排序',
    'col_created_at' => '建立時間',

    // Filter
    'filter_placeholder' => '依標題搜尋…',

    // Pagination
    'per_page' => '每頁',
    'total_selected' => '已選取 :n 個相簿|已選取 :n 個相簿',
    'select_all_page' => '選取此頁全部',
    'select_all_matching' => '選取所有符合項目',
    'cap_warning' => '僅選取了前 1,000 個相簿。',

    // Mode toggle
    'mode_paginated' => '分頁',
    'mode_infinite' => '無限捲動',

    // Action buttons
    'action_delete' => '刪除',
    'action_set_owner' => '設定擁有者',
    'action_edit_fields' => '編輯欄位',

    // Edit Fields modal
    'edit_fields_title' => '編輯欄位',
    'edit_fields_description' => '只會更新已勾選的欄位。空白值會清除該欄位。',
    'section_metadata' => '中繼資料',
    'section_visibility' => '可見性',
    'field_description' => '描述',
    'field_copyright' => '版權',
    'field_license' => '授權',
    'field_photo_layout' => '照片版面配置',
    'field_photo_sorting_col' => '照片排序欄位',
    'field_photo_sorting_order' => '照片排序順序',
    'field_album_sorting_col' => '相簿排序欄位',
    'field_album_sorting_order' => '相簿排序順序',
    'field_album_thumb_aspect_ratio' => '縮圖長寬比',
    'field_album_timeline' => '相簿時間軸',
    'field_photo_timeline' => '照片時間軸',
    'field_published_at' => 'Flow 發布日期',
    'field_is_nsfw' => '敏感',
    'field_is_public' => '公開',
    'field_is_link_required' => '需要連結',
    'field_grants_full_photo_access' => '完整照片存取',
    'field_grants_download' => '下載',
    'field_grants_upload' => '上傳（SE）',
    'apply' => '套用',
    'cancel' => '取消',

    // Set Owner modal
    'set_owner_title' => '設定擁有者',
    'set_owner_description' => '所有選取的相簿將移至根層級，其下的子相簿與照片也會一併轉移。',
    'set_owner_select_user' => '選擇新擁有者',
    'transfer' => '轉移',

    // Delete confirmation modal
    'delete_title' => '刪除相簿',
    'delete_confirm' => '您即將永久刪除 :count 個相簿及其所有子相簿與照片。此操作無法復原。|您即將永久刪除 :count 個相簿及其所有子相簿與照片。此操作無法復原。',
    'confirm_delete' => '確認刪除',

    // Toasts
    'success_patch' => '已成功更新相簿。',
    'success_set_owner' => '已成功轉移擁有權。',
    'success_delete' => '已成功刪除相簿。',
    'error_load' => '載入相簿失敗。',
    'error_load_ids' => '載入相簿 ID 失敗。',
    'error_patch' => '更新相簿失敗。',
    'error_missing_timezone' => '請為發布日期選擇時區。',
    'error_set_owner' => '轉移擁有權失敗。',
    'error_delete' => '刪除相簿失敗。',
    'error_load_users' => '載入使用者失敗。',
];
