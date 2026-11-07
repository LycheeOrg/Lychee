<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Gallery sizes: preview page and live drawer (Feature 110)
    |--------------------------------------------------------------------------
    */

    'title' => '圖庫尺寸',
    'live_title' => '圖庫尺寸(即時)',
    'live_hint' => '變更會套用到此面板後方的圖庫。未儲存就關閉會捨棄變更。',
    'spotlight_action' => '調整圖庫尺寸(即時)',
    'not_allowed' => '只有管理員可以變更圖庫尺寸。',
    'section_albums' => '相簿',
    'section_photos' => '照片',
    'section_placeholders' => '預留位置',
    'album_min_width' => '最小圖塊寬度',
    'album_aspect_ratio' => '長寬比',
    'albums_per_row_mobile' => '手機上每列數量',
    'layout' => '版面',
    'layouts' => [
        'justified' => '左右對齊',
        'masonry' => '瀑布流',
        'square' => '方形',
        'grid' => '網格',
    ],
    'gap' => '間距',
    'justified_row_height' => '列高(左右對齊)',
    'masonry_column_width' => '欄寬(瀑布流)',
    'grid_column_width' => '欄寬(網格)',
    'square_column_width' => '欄寬(方形)',
    'px' => ':value px',
    'album_count' => '相簿',
    'photo_count' => '照片',
    'shuffle' => '隨機排列',
    'album_title' => '相簿 :n',
    'width' => '寬度',
    'presets' => [
        'phone' => '手機',
        'tablet' => '平板',
        'laptop' => '筆電',
        'desktop' => '桌機',
        'wide' => '寬螢幕',
        'window' => '此視窗',
    ],
    'shown_at' => ':width px · 以 :scale % 顯示',
    'summary' => '每列 :albums 個相簿 · :rows 列照片',
    'unsaved' => '未儲存的變更',
    'save' => '儲存',
    'revert' => '還原',
    'close' => '關閉',
    'saved' => '已儲存圖庫尺寸',
    'save_error' => '無法儲存圖庫尺寸',
    'load_error' => '無法載入設定',
    'reset_hint' => '按兩下以還原已儲存的值',
    'live_empty' => '此頁面上沒有使用這些尺寸的內容。',
];
