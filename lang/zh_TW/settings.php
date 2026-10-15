<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings page
    |--------------------------------------------------------------------------
    */
    'title' => '設定',
    'small_screen' => '為了在此頁面獲得更好的體驗，<br />建議您使用較大的螢幕。',
    'tabs' => [
        'basic' => '基本',
        'all_settings' => '所有設定',
    ],
    'toasts' => [
        'change_saved' => '變更已儲存！',
        'details' => '已依照要求修改設定',
        'error' => '錯誤！',
        'error_load_css' => '無法載入 dist/user.css',
        'error_load_js' => '無法載入 dist/custom.js',
        'error_save_css' => '無法儲存 CSS',
        'error_save_js' => '無法儲存 JS',
        'thank_you' => '感謝您的支持。',
        'reload' => '請重新載入頁面以使用完整功能。',
    ],
    'system' => [
        'header' => '系統',
        'use_dark_mode' => 'Lychee 使用深色模式',
        'language' => 'Lychee 使用的語言',
        'nsfw_album_visibility' => '預設顯示敏感相簿。',
        'nsfw_album_explanation' => '若相簿為公開，仍可存取，只是在檢視中隱藏，<b>按下 <kbd>H</kbd> 即可顯示</b>。',
        'cache_enabled' => '啟用回應快取。',
        'cache_enabled_details' => '這將大幅加快 Lychee 的回應速度。<br> <i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i>若您使用密碼保護的相簿，請勿啟用此功能。',
    ],
    'lychee_se' => [
        'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
        'call4action' => '取得專屬功能並支持 Lychee 的開發。解鎖 <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">SE 版本</a>。',
        'preview' => '啟用 Lychee SE 功能預覽',
        'hide_call4action' => '隱藏此 Lychee SE 註冊表單。我對目前的 Lychee 很滿意。:)',
        'hide_warning' => '啟用後，只能透過上方的「更多」分頁註冊您的授權金鑰。變更將在頁面重新載入後套用。',
    ],
    'dropbox' => [
        'header' => 'Dropbox',
        'instruction' => '若要從 Dropbox 匯入照片，您需要從其網站取得有效的 drop-ins 應用程式金鑰。',
        'api_key' => 'Dropbox API 金鑰',
        'set_key' => '設定 Dropbox 金鑰',
    ],
    'gallery' => [
        'header' => '圖庫',
        'photo_order_column' => '照片排序的預設欄位',
        'photo_order_direction' => '照片排序的預設順序',
        'album_order_column' => '相簿排序的預設欄位',
        'album_order_direction' => '相簿排序的預設順序',
        'aspect_ratio' => '相簿縮圖的預設長寬比',
        'photo_layout' => '照片版面配置',
        'album_decoration' => '在相簿封面上顯示裝飾（子相簿和／或照片數量）',
        'album_decoration_direction' => '水平或垂直對齊相簿裝飾',
        'photo_overlay' => '預設圖片疊加資訊',
        'album_date_scrubber_enabled' => '在相簿檢視中顯示日期捲動軸',
        'rounded_corners_enabled' => '照片與相簿縮圖使用圓角',
        'album_border_enabled' => '在照片與相簿縮圖周圍顯示邊框',
        'photo_ken_burns_on_hover_enabled' => '滑鼠懸停於照片時啟用 Ken Burns 效果',
        'photo_ken_burns_on_hover_scale' => 'Ken Burns 懸停縮放幅度（%）',
        'photo_ken_burns_on_hover_duration' => 'Ken Burns 懸停縮放時間（秒）',
        'license_default' => '相簿預設使用的授權',
        'license_help' => '需要協助選擇嗎？',
    ],
    'geolocation' => [
        'header' => '地理位置',
        'map_display' => '根據 GPS 座標顯示地圖',
        'map_display_public' => '允許匿名使用者存取地圖',
        'map_provider' => '設定地圖提供者',
        'map_include_subalbums' => '在地圖上包含子相簿的照片',
        'location_decoding' => '使用 GPS 位置解碼',
        'location_show' => '顯示從 GPS 座標解析出的位置',
        'location_show_public' => '匿名使用者可存取從 GPS 座標解析出的位置',
        'gps_coordinate_display' => '顯示 GPS 座標',
        'gps_coordinate_display_public' => '允許匿名使用者存取 GPS 座標',
    ],
    'cssjs' => [
        'header' => '自訂 CSS 與 JS',
        'change_css' => '變更 CSS',
        'change_js' => '變更 JS',
    ],
    'all' => [
        'old_setting_style' => '舊版設定樣式',
        'expert_settings' => '專家模式',
        'change_detected' => '部分設定已變更。',
        'save' => '儲存',
        'back_to_settings' => '返回分組設定',
    ],
    'tool_option' => [
        'disabled' => '已停用',
        'enabled' => '已啟用',
        'discover' => '自動偵測',
    ],
    'groups' => [
        'general' => '一般',
        'system' => '系統',
        'modules' => '模組',
        'advanced' => '進階',
    ],
    'config' => [
        'use_admin_dashboard' => [
            'label' => '使用管理儀表板',
            'help' => '以單一連結指向新的管理儀表板頁面，取代巢狀的管理子選單。',
        ],
    ],
    'password_field' => [
        'set' => '已設定',
        'not_set' => '未設定',
        'placeholder' => '新密碼',
        'clear' => '清除',
    ],
];
