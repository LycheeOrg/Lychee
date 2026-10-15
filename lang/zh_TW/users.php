<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => '使用者',
    'description' => '您可以在此管理 Lychee 的使用者，包括建立、編輯及刪除使用者。',
    'create' => '建立新使用者',
    'username' => '使用者名稱',
    'password' => '密碼',
    'legend' => '圖例',
    'upload_rights' => '勾選後，使用者可以上傳內容。',
    'edit_rights' => '勾選後，使用者可以修改個人資料（使用者名稱、密碼）。',
    'upload_trust_level' => '上傳信任等級 — 控制上傳內容是否立即公開。',

    'quota' => '設定後，使用者的相片將有空間配額限制（單位 kB）。',
    'user_deleted' => '使用者已刪除',
    'user_created' => '使用者已建立',
    'user_updated' => '使用者已更新',
    'change_saved' => '變更已儲存！',
    'create_edit' => [
        'upload_rights' => '使用者可以上傳內容。',
        'edit_rights' => '使用者可以修改個人資料（使用者名稱、密碼）。',
        'admin_rights' => '使用者具有管理員權限。',
        'upload_trust_level' => '上傳信任等級',
        'upload_trust_level_check' => '檢查 – 上傳內容需經管理員核准才會公開。',
        'upload_trust_level_monitor' => '監控 – 上傳內容預設公開，除非內容被標記。',
        'upload_trust_level_trust_but_verify' => '信任但驗證 – 上傳內容公開；審查類結果自動核准，封鎖類結果可設定。',
        'upload_trust_level_trusted' => '信任 – 上傳內容立即公開。',
        'trust_level_options' => [
            'trusted' => '信任',
            'trust_but_verify' => '信任但驗證',
            'monitor' => '監控',
            'check' => '檢查',
        ],
        'quota' => '使用者有配額限制。',
        'quota_kb' => '配額（kB，0 表示預設值）',
        'note' => '管理員備註（不公開顯示）',
        'create' => '建立',
        'edit' => '編輯',
    ],
    'invite' => [
        'button' => '邀請使用者',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => '此連結有效期限為 %d 天。',
    ],
    'line' => [
        'owner' => '擁有者',
        'admin' => '管理員',
        'edit' => '編輯',
        'delete' => '刪除',
    ],
];
