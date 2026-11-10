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

    'title' => '額外連結',
    'description' => '管理顯示在首頁導覽列及／或頁尾的額外連結清單，可自由排序。',

    // Empty state
    'no_links' => '尚未設定任何額外連結。',
    'create_first' => '建立您的第一個連結',

    // Table columns
    'col_label' => '標籤',
    'col_url' => 'URL',
    'col_placement' => '位置',
    'col_enabled' => '已啟用',
    'col_actions' => '操作',

    // Placement labels
    'placement_nav' => '導覽列',
    'placement_footer' => '頁尾',
    'placement_both' => '導覽列與頁尾',

    // Built-in links (Gallery, Contact)
    'badge_built_in' => '內建',
    'built_in_cannot_delete' => '此內建連結無法刪除。您可以停用它，或像其他連結一樣調整順序。',
    'built_in_url_hint' => '這是內建連結，無法變更其目標。',

    // Buttons
    'create' => '建立連結',
    'edit' => '編輯',
    'delete' => '刪除',
    'cancel' => '取消',
    'save' => '儲存',

    // Form fields
    'field_label' => '標籤',
    'field_label_placeholder' => '例如：Instagram',
    'field_url' => 'URL',
    'field_url_placeholder' => 'https://example.com',
    'field_placement' => '位置',
    'field_open_in_new_tab' => '在新分頁開啟',
    'field_enabled' => '已啟用',

    // Modal titles
    'modal_create_title' => '建立連結',
    'modal_edit_title' => '編輯連結',

    // Delete confirmation
    'confirm_delete_header' => '刪除連結',
    'confirm_delete_message' => '確定要刪除連結「:label」嗎？此操作無法復原。',

    // Toasts
    'created' => '連結已成功建立。',
    'updated' => '連結已成功更新。',
    'deleted' => '連結已成功刪除。',
    'reordered' => '連結已成功重新排序。',
    'error_load' => '載入連結失敗。',
    'error_save' => '儲存連結失敗。',
    'error_delete' => '刪除連結失敗。',
    'error_reorder' => '重新排序連結失敗。',
];
