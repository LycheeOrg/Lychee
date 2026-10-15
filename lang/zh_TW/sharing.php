<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => '分享',
    'info' => '本頁提供相簿分享權限的總覽，並可加以編輯。',
    'album_title' => '相簿標題',
    'username' => '使用者名稱',
    'no_data' => '分享清單是空的。',
    'filter_placeholder' => '依相簿標題搜尋...',
    'hide_empty' => '隱藏未分享的相簿',
    'screen_too_small' => '此頁面需要較大的螢幕。',
    'share' => '分享',
    'add_new_access_permission' => '新增存取權限',
    'permission_deleted' => '權限已刪除！',
    'permission_created' => '權限已建立！',
    'propagate' => '套用至子項',
    'propagate_help' => '將目前的存取權限套用至所有子孫相簿<br>（子相簿及其各自的子相簿等）',
    'propagate_default' => '預設會更新現有的權限（相簿－使用者）<br>並新增缺少的權限。<br>不在此清單中的其他權限將保持不變。',
    'propagate_overwrite' => '覆寫現有權限而非更新。<br>這也會移除所有不在此清單中的權限。',
    'propagate_warning' => '此操作無法復原。',
    'permission_overwritten' => '套用成功！權限已覆寫！',
    'permission_updated' => '套用成功！權限已更新！',
    'bluk_share' => '批次分享',
    'bulk_share_instr' => '選擇多個相簿及要分享的使用者。',
    'albums' => '相簿',
    'users' => '使用者',
    'no_users' => '沒有可選擇的使用者。',
    'no_albums' => '沒有可選擇的相簿。',
    'grants' => [
        'read' => '授予讀取權限',
        'original' => '授予原始相片存取權限',
        'download' => '授予下載權限',
        'upload' => '授予上傳權限',
        'edit' => '授予編輯權限',
        'move' => '授予移動、複製及合併權限',
        'delete' => '授予刪除權限',
    ],
];
